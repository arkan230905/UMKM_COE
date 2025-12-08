<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateUserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Updating user roles...');

        // Get all users
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found in database.');
            return;
        }

        $this->command->info("Found {$users->count()} user(s)");

        // Update first user to owner
        $firstUser = $users->first();
        $firstUser->role = 'owner';
        $firstUser->save();

        $this->command->info("✓ Updated user ID {$firstUser->id} ({$firstUser->email}) to role: owner");

        // Display all users
        $this->command->info("\nCurrent users:");
        $this->command->table(
            ['ID', 'Name', 'Email', 'Role'],
            $users->map(function ($user) {
                return [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->role
                ];
            })
        );

        $this->command->info("\n✓ Done! You can now login and access the dashboard.");
    }
}
