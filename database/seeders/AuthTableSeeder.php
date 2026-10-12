<?php

namespace Database\Seeders;
use Database\Seeders\PermissionRoleTableSeeder;
use Database\Seeders\UserRoleTableSeeder;
use Database\Seeders\UsersTableSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AuthTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UsersTableSeeder::class);
        $this->call(PermissionRoleTableSeeder::class);
        $this->call(UserRoleTableSeeder::class);
    }
}
