<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Vyrko Dev',
            'email' => 'hectorcoelho@hotmail.com',
            'password' => '123mudar',
            'plan_name' => 'free',
        ]);
    }
}
