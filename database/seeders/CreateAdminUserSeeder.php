<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class CreateAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => 'James',
                'email' => 'james@gmail.com',
                'password' => bcrypt('a123456')
            ]);

            $role = Role::create(['name' => 'Admin']);
            $permissions = Permission::pluck('id','id')->all();

            $role->syncPermissions($permissions);

            $user->assignRole($role);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            var_dump($e);
        }

    }
}
