<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name'  => 'Caecario Yonim Betta Sabillah',
            'email' => 'caecarioyonim@gmail.com',
            'type' => '0',
            'password'  => Hash::make('arwapada008'),
        ]);
    }
}
