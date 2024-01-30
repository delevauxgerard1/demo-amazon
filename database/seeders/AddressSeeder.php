<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();

        Address::create([
            'user_id' => $user->id,
            'addr1' => '2844 S Eagle Rd',
            'city' => 'Newtown',
            'postcode' => '72113',
            'country' => 'United States'
        ]);
    }
}
