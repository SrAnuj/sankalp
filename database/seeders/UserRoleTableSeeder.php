<?php

namespace Database\Seeders;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserRoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::findOrFail(1)->assignRole('super admin');
        User::findOrFail(2)->assignRole('wholesaler');
        User::findOrFail(3)->assignRole('warehouse');
        User::findOrFail(4)->assignRole('retailer');
        User::findOrFail(5)->assignRole('distributor');
        User::findOrFail(6)->assignRole('employee');
        User::findOrFail(7)->assignRole('user');

    }
}
