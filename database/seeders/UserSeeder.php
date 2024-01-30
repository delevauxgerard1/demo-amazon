<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'first_name' => 'Jhon',
            'last_name' => 'Doe',
            'email' => 'jhondoe@mail.com',
            'password' => Hash::make('jhon1234')
        ]);
    }
}
