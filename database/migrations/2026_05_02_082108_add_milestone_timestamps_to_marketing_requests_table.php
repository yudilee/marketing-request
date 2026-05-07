<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('marketing_requests', function (Blueprint $table) {
            $table->json('milestone_timestamps')->nullable()->after('production_milestone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_requests', function (Blueprint $table) {
            $table->dropColumn('milestone_timestamps');
        });
    }
};
