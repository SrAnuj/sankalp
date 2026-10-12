<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionRoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Define roles
        $roles = [
            'super admin',
            'distributor',
            'retailer',
            'employee',
            'wholesaler',
            'warehouse',
            'user',
        ];

        // Create or update roles
        $roleModels = [];
        foreach ($roles as $roleName) {
            $roleModels[$roleName] = Role::firstOrCreate(['name' => $roleName]);
        }

        // Define all permissions (merge both old and new sets)

        $permissions = [
            // Core
            'show_dashboard',

            // Users
            'show_users',
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            'block_users',
            'bulk_delete_users',
            'export_users',
            'import_users',

            // Naam Library
            'show_naam_library',
            'view_naam_library',
            'create_naam_library',
            'edit_naam_library',
            'delete_naam_library',
            'bulk_delete_naam_library',

            // Sankalp
            'show_sankalps',
            'view_sankalps',
            'create_sankalps',
            'edit_sankalps',
            'delete_sankalps',
            'bulk_delete_sankalps',
            'export_sankalps',

            // Naam Lekhan
            'show_naam_lekhans',
            'view_naam_lekhans',
            'edit_naam_lekhans',
            'delete_naam_lekhans',
            'export_naam_lekhans',

            // Progress, Streaks and Milestones
            'show_user_progress',
            'view_user_progress',
            'edit_user_progress',
            'show_streaks',
            'view_streaks',
            'show_milestones',
            'view_milestones',

            // Naam Pustika
            'show_naam_pustikas',
            'view_naam_pustikas',
            'create_naam_pustikas',
            'edit_naam_pustikas',
            'delete_naam_pustikas',
            'manage_pustika_customization',
            'manage_pustika_preview',

            // Orders
            'show_orders',
            'view_orders',
            'create_orders',
            'edit_orders',
            'delete_orders',
            'bulk_delete_orders',
            'export_orders',
            'pdf_order_invoice',
            'pdf_packaging_slip',

            // Payments
            'show_payments',
            'view_payments',
            'verify_payments',
            'refund_payments',
            'export_payments',

            // Shipping and Printing
            'show_shipments',
            'view_shipments',
            'create_shipments',
            'edit_shipments',
            'update_shipment_status',
            'manage_printing_status',
            'manage_tracking_details',

            // Gifts
            'show_gifts',
            'view_gifts',
            'create_gifts',
            'edit_gifts',
            'delete_gifts',
            'manage_gift_customization',

            // Notifications and Reminders
            'show_notifications',
            'view_notifications',
            'create_notifications',
            'edit_notifications',
            'delete_notifications',
            'send_notifications',
            'manage_reminders',
            'manage_fcm_notifications',
            'mark_notifications_as_read',
            'bulk_delete_notifications',

            // Reports and Analytics
            'show_reports',
            'view_reports',
            'export_reports',
            'show_analytics',
            'view_analytics',

            // Roles
            'show_roles',
            'view_roles',
            'create_roles',
            'edit_roles',
            'delete_roles',
            'bulk_delete_roles',

            // Permissions
            'show_permissions',
            'view_permissions',
            'create_permissions',
            'edit_permissions',
            'delete_permissions',

            // General and App Settings
            'show_general_settings',
            'edit_general_settings',
            'manage_app_settings',
            'manage_languages',
            'manage_naam_content',

            // Profile
            'login_profile',
            'view_profile',
            'save_profile',
            'change_password',

            // Activity Logs
            'show_activity_logs',
            'view_activity_logs',
            'export_activity_logs',
        ];

        // Create permissions
        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName]);
        }

        // Assign permissions to roles
        $superAdminRole = $roleModels['super admin'];
        $superAdminRole->syncPermissions(Permission::all()); // super admin gets all permissions

        // Other roles get only 'view_backend' by default
        $basicPermission = Permission::firstOrCreate(['name' => 'view_backend']);
        foreach (['distributor', 'retailer', 'employee', 'wholesaler', 'warehouse', 'user'] as $roleName) {
            $roleModels[$roleName]->syncPermissions([$basicPermission]);
        }
    }
}
