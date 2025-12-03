<?php

use App\Models\Module;
use App\Models\Permission;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        $module = Module::firstOrCreate(['module_name' => 'audit']);

        $permissions = [
            // Audit Template Permissions
            [
                'name' => 'add_audit_template',
                'display_name' => 'Add Audit Template',
                'module_id' => $module->id,
                'is_custom' => 1,
                'allowed_permissions' => Permission::ALL_NONE,
            ],
            [
                'name' => 'view_audit_template',
                'display_name' => 'View Audit Template',
                'module_id' => $module->id,
                'is_custom' => 1,
                'allowed_permissions' => Permission::ALL_4_ADDED_1_NONE_5,
            ],
            [
                'name' => 'edit_audit_template',
                'display_name' => 'Edit Audit Template',
                'module_id' => $module->id,
                'is_custom' => 1,
                'allowed_permissions' => Permission::ALL_4_ADDED_1_NONE_5,
            ],
            [
                'name' => 'delete_audit_template',
                'display_name' => 'Delete Audit Template',
                'module_id' => $module->id,
                'is_custom' => 1,
                'allowed_permissions' => Permission::ALL_4_ADDED_1_NONE_5,
            ],

            // Audit Permissions
            [
                'name' => 'add_audit',
                'display_name' => 'Add Audit',
                'module_id' => $module->id,
                'is_custom' => 0,
                'allowed_permissions' => Permission::ALL_NONE,
            ],
            [
                'name' => 'view_audit',
                'display_name' => 'View Audit',
                'module_id' => $module->id,
                'is_custom' => 0,
                'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
            ],
            [
                'name' => 'edit_audit',
                'display_name' => 'Edit Audit',
                'module_id' => $module->id,
                'is_custom' => 0,
                'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
            ],
            [
                'name' => 'delete_audit',
                'display_name' => 'Delete Audit',
                'module_id' => $module->id,
                'is_custom' => 0,
                'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
            ],

            // Audit Settings Permissions
            [
                'name' => 'manage_audit_settings',
                'display_name' => 'Manage Audit Settings',
                'module_id' => $module->id,
                'is_custom' => 1,
                'allowed_permissions' => Permission::ALL_NONE,
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $module = Module::where('module_name', 'audit')->first();

        if ($module) {
            Permission::where('module_id', $module->id)->delete();
            $module->delete();
        }
    }
};

