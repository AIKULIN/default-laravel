<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::beginTransaction();
        try {
            $this->call(PermissionTableSeeder::class);
            $this->call(CreateAdminUserSeeder::class);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            var_dump($e);
        }
    }
}
