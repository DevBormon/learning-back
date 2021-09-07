<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\User;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("SET FOREIGN_KEY_CHECKS = 0");
        User::truncate();
        DB::table('users')->insert([
            [
                'name' => 'Bormon',
                'email' => 'bormon@yopmail.com',
                'password' => Hash::make('12345678'),
            ],[
                'name' => 'Dev',
                'email' => 'dev@yopmail.com',
                'password' => Hash::make('87654321'),
            ]
        ]);
    }
}
