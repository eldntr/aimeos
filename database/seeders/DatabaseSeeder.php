<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $email = env('ADMIN_EMAIL', 'admin@reborns.id');
        $password = env('ADMIN_PASSWORD', 'admin123');
        
        $user = \App\Models\User::where('email', $email)->first();
        if (!$user) {
            \Illuminate\Support\Facades\Artisan::call('aimeos:account', [
                'email' => $email,
                '--password' => $password,
                '--admin' => true,
            ]);
            $this->command->info("Admin created: $email");
        } else {
            $this->command->info("Admin $email already exists.");
        }
    }
}
