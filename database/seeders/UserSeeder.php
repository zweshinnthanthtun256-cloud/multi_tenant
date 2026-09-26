<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('INITIAL_ADMIN_EMAIL');
        $password = env('INITIAL_ADMIN_PASSWORD');
        if (! $email || ! $password) {
            $this->command?->warn('Set INITIAL_ADMIN_EMAIL and INITIAL_ADMIN_PASSWORD to create the initial administrator.');

            return;
        }
        if (strlen($password) < 12) {
            throw new \RuntimeException('Initial administrator password must have at least 12 characters.');
        }
        $user = User::firstOrCreate(['email' => strtolower($email)], ['name' => 'Platform Admin', 'password' => Hash::make($password), 'status' => 'active']);
        $user->assignRole(Role::findOrCreate('Super Admin', 'web'));
    }
}
