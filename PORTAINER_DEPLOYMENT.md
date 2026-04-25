# User Management & Portainer Deployment

This document explains how to manage users and deploy the application using Portainer.

## 1. Creating a User via Command Line (Portainer Console)

If the application is already running and you need to create a new user without resetting the database, follow these steps:

1.  Open **Portainer** and go to **Containers**.
2.  Find the `app` container and click the **Console** (`>_`) icon.
3.  Connect using `bash` or `sh`.
4.  Run the following command to enter Laravel Tinker:
    ```bash
    php artisan tinker
    ```
5.  Paste the following snippet (edit the details as needed):
    ```php
    User::create([
        'name' => 'Your Name',
        'username' => 'your_username',
        'email' => 'your@email.com',
        'password' => Hash::make('your_password'),
        'role' => 'admin', // Options: admin, marcom, manager, staff
        'department_id' => 1
    ]);
    ```
6.  Type `exit` to finish.

## 2. Managing Users via Seeders

The default user creation is handled in `database/seeders/DatabaseSeeder.php`. 

### To Update the Default User:
1.  Modify the `User::firstOrCreate` block in `DatabaseSeeder.php`.
2.  In the Portainer Console, run:
    ```bash
    php artisan db:seed
    ```

### Available Roles
- `admin`: Full access to all features.
- `marcom`: Marketing communication team access.
- `manager`: Branch manager access.
- `staff`: Basic sales staff access.

## 3. Portainer Stack Deployment

When deploying as a stack, ensure your environment variables are set correctly in the Portainer UI:

- `DB_USERNAME`: Database user (default: `root`)
- `DB_PASSWORD`: Database password
- `APP_KEY`: Application encryption key
- `APP_URL`: The URL where the app is hosted

To refresh the application after a code change:
1.  Go to the **Stack** in Portainer.
2.  Click **Editor**.
3.  Click **Update the stack** and toggle **Re-pull image** if using a custom registry.
