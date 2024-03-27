<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LetterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $letters = [
            [
                'received_date' => now(),
                'letters_type' => 'Type A',
                'reference_number' => 'REF001',
                'letter_date' => now(),
                'from' => 'Sender A',
                'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                'user_id' => 1, // User ID of the sender
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'received_date' => now(),
                'letters_type' => 'Type B',
                'reference_number' => 'REF002',
                'letter_date' => now(),
                'from' => 'Sender B',
                'description' => 'Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                'user_id' => 2, // User ID of the sender
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Add more dummy data as needed
        ];

        // Insert data into the letter table
        DB::table('letters')->insert($letters);
    }
}
