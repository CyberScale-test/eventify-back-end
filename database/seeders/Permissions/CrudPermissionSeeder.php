<?php

namespace Database\Seeders\Permissions;

use App\Services\ACLService;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Enums\ROLE as ROLE_Enum;


class CrudPermissionSeeder extends Seeder
{

    public function __construct(protected ACLService $aclService) {}

    public function run()
    {


        $adminRole = Role::where('name', ROLE_ENUM::ADMIN)->first();
        $userRole = Role::where('name', Role_ENUM::USER)->first();



        //My BODYGUARD
        if (!$adminRole) {
            $adminRole = $this->aclService->createRole(ROLE_ENUM::ADMIN);
        }


        // Here, include project specific permissions
        $this->aclService->createScopePermissions('users', ['create', 'read', 'update', 'delete', 'import', 'export']);
        $this->aclService->createScopePermissions('events', ['create', 'read', 'read_own',  'update', 'delete']); // add read_own later here

        // Assign permissions to roles based on what they should have access to
        $this->aclService->assignScopePermissionsToRole($adminRole, 'users', ['create', 'read', 'update', 'delete', 'import', 'export']);
        $this->aclService->assignScopePermissionsToRole($adminRole, 'events', ['create', 'read', 'update', 'delete']);

        // User can manage events but not users
        $this->aclService->assignScopePermissionsToRole($userRole, 'events', ['create', 'read', 'update', 'delete']);
    }
}
