<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Approval;
use App\Models\Department;
use App\Models\MarketingRequest;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function index()
    {
        $requests = MarketingRequest::with(['user', 'department'])
            ->whereIn('status', ['submitted', 'under_review'])
            ->latest()
            ->paginate(15);

        return view('approvals.index', compact('requests'));
    }

    public function all(Request $request)
    {
        $query = MarketingRequest::with(['user', 'department'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $requests    = $query->paginate(20);
        $departments = Department::orderBy('name')->get();

        return view('approvals.all', compact('requests', 'departments'));
    }

    public function show(MarketingRequest $request)
    {
        $request->load(['user', 'department', 'reviewer', 'approvals.approver', 'comments.user']);
        return view('approvals.show', compact('request'));
    }

    public function decide(Request $httpRequest, MarketingRequest $request)
    {
        $validated = $httpRequest->validate([
            'status'  => 'required|in:approved,rejected',
            'comment' => 'nullable|string|max:1000|required_if:status,rejected',
        ]);

        $user = auth()->user();

        // Only legitimate approver roles can act
        if (!$user->canApprove()) {
            abort(403);
        }

        // The request creator cannot approve their own request
        if ($request->user_id === $user->id) {
            return back()->with('error', 'You cannot approve your own request.');
        }

        // Prevent the same person acting twice on the same request
        if ($request->approvals()->where('approver_id', $user->id)->exists()) {
            return back()->with('error', 'You have already acted on this request.');
        }

        // --- Stage-based role gating ---
        // Get which roles have already approved (before this action)
        $existingApprovedRoles = $request->approvals()
            ->where('status', 'approved')
            ->with('approver')
            ->get()
            ->pluck('approver.role')
            ->map(fn($r) => $r instanceof Role ? $r->value : $r)
            ->toArray();

        if ($request->is_local_campaign) {
            $hasManager = in_array('manager', $existingApprovedRoles);
            if (!$hasManager) {
                // Step 1: Sales Manager OR Aftersales Manager can approve
                $userDept = strtolower($user->department?->name ?? '');
                $isSalesMgr      = $user->role === Role::Manager && str_contains($userDept, 'sales');
                $isAftersalesMgr = $user->role === Role::Manager && str_contains($userDept, 'aftersales');
                if (!$isSalesMgr && !$isAftersalesMgr) {
                    return back()->with('error', 'Step 1 requires approval from either the Sales Manager or Aftersales Manager. It is not your turn yet.');
                }
            }
            if ($hasManager && $user->role !== Role::Gm) {
                return back()->with('error', 'Step 2 requires GM / Branch Manager approval. You cannot act at this stage.');
            }
        } elseif ($request->is_group_campaign) {
            $hasMarcom  = in_array('marcom', $existingApprovedRoles);
            $hasManager = in_array('manager', $existingApprovedRoles);
            if (!$hasMarcom) {
                // Step 1: Marketing Corporate (Marcom) must approve first
                if ($user->role !== Role::Marcom) {
                    return back()->with('error', 'Step 1 requires Marketing Corporate approval. It is not your turn yet.');
                }
            } elseif (!$hasManager) {
                // Step 2: Aftersales Manager must approve second
                $userDept = strtolower($user->department?->name ?? '');
                if ($user->role !== Role::Manager || !str_contains($userDept, 'aftersales')) {
                    return back()->with('error', 'Step 2 requires the Aftersales Manager approval. It is not your turn yet.');
                }
            } elseif ($user->role !== Role::Director) {
                // Step 3: Director must approve last
                return back()->with('error', 'Step 3 requires Director sign-off. You cannot act at this stage.');
            }
        }
        // No campaign type → any canApprove() role can finalize in one step

        // Log the approval action first
        Approval::create([
            'marketing_request_id' => $request->id,
            'approver_id'          => $user->id,
            'status'               => $validated['status'],
            'comment'              => $validated['comment'] ?? null,
            'acted_at'             => now(),
        ]);

        // Rejection always finalises immediately
        if ($validated['status'] === 'rejected') {
            $request->status          = 'rejected';
            $request->manager_comment = $validated['comment'] ?? null;
            $request->reviewed_by     = $user->id;
            $request->reviewed_at     = now();
            $request->save();

            return redirect()->route('approvals.index')
                ->with('success', 'Request #' . str_pad($request->id, 4, '0', STR_PAD_LEFT) . ' has been rejected.');
        }

        // Collect all approved roles so far (including the one just created)
        $approvedRoles = $request->approvals()
            ->where('status', 'approved')
            ->with('approver')
            ->get()
            ->pluck('approver.role')
            ->map(fn($r) => $r instanceof Role ? $r->value : $r)
            ->toArray();

        // Determine whether all required sign-offs are complete
        if ($request->is_local_campaign) {
            // Lokal: Sales Manager (manager) AND GM/Branch Manager (gm)
            $hasManager  = in_array('manager', $approvedRoles);
            $hasGm       = in_array('gm', $approvedRoles);
            $isFinalized = $hasManager && $hasGm;
        } elseif ($request->is_group_campaign) {
            // Group: Marketing Corporate → Aftersales Manager → Director
            $hasMarcom   = in_array('marcom', $approvedRoles);
            $hasManager  = in_array('manager', $approvedRoles);
            $hasDirector = in_array('director', $approvedRoles);
            $isFinalized = $hasMarcom && $hasManager && $hasDirector;
        } else {
            // No campaign type set — single approval is enough
            $isFinalized = true;
        }

        if ($isFinalized) {
            $request->status                 = 'approved';
            $request->production_status      = 'pending'; // auto-start production queue
            $request->production_updated_at  = now();
            $request->manager_comment        = $validated['comment'] ?? null;
            $request->reviewed_by            = $user->id;
            $request->reviewed_at            = now();
        } else {
            $request->status = 'under_review';
            if (!$request->reviewed_by) {
                $request->reviewed_by = $user->id;
                $request->reviewed_at = now();
            }
        }

        $request->save();

        $label = $isFinalized ? 'fully approved' : 'approved — awaiting further sign-off';
        return redirect()->route('approvals.index')
            ->with('success', 'Request #' . str_pad($request->id, 4, '0', STR_PAD_LEFT) . ' has been ' . $label . '.');
    }
}
