<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Factory::create();
        // Add the master administrator, user id of 1
        $users = [
            [

                'name' => 'Super Admin',
                'email' => 'admin@admin.com',
                'password' => Hash::make('secret'),
                'phone' => $faker->phoneNumber,
                'avatar' => 'img/default-avatar.jpg',
                'gender' => $faker->randomElement(['Male', 'Female', 'Other']),
                'status' => 1,
                'email_verified_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'wholesaler',
                'email' => 'wholesaler@wholesaler.com',
                'password' => Hash::make('secret'),
                'phone' => $faker->phoneNumber,
                'avatar' => 'img/default-avatar.jpg',
                'gender' => $faker->randomElement(['Male', 'Female', 'Other']),
                'status' => 1,
                'email_verified_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'warehouse',
                'email' => 'warehouse@warehouse.com',
                'password' => Hash::make('secret'),
                'phone' => $faker->phoneNumber,
                'avatar' => 'img/default-avatar.jpg',
                'status' => 1,
                'gender' => $faker->randomElement(['Male', 'Female', 'Other']),
                'email_verified_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'retailer',
                'email' => 'retailer@retailer.com',
                'password' => Hash::make('secret'),
                'phone' => $faker->phoneNumber,
                'avatar' => 'img/default-avatar.jpg',
                'status' => 1,
                'gender' => $faker->randomElement(['Male', 'Female', 'Other']),
                'email_verified_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'distributor',
                'email' => 'distributor@distributor.com',
                'password' => Hash::make('secret'),
                'phone' => $faker->phoneNumber,
                'avatar' => 'img/default-avatar.jpg',
                'status' => 1,
                'gender' => $faker->randomElement(['Male', 'Female', 'Other']),
                'email_verified_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'employee',
                'email' => 'employee@employee.com',
                'password' => Hash::make('secret'),
                'phone' => $faker->phoneNumber,
                'avatar' => 'img/default-avatar.jpg',
                'status' => 1,
                'gender' => $faker->randomElement(['Male', 'Female', 'Other']),
                'email_verified_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'User',
                'email' => 'user@user.com',
                'password' => Hash::make('secret'),
                'phone' => $faker->phoneNumber,
                'avatar' => 'img/default-avatar.jpg',
                'gender' => $faker->randomElement(['Male', 'Female', 'Other']),
                'status' => 1,
                'email_verified_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];
        foreach ($users as $user_data) {
            $user = User::create($user_data);

        }
    }
}
