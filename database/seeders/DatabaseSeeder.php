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
            'name' => 'Администратор АРМ',
            'email' => 'admin@arm.local',
            'password' => bcrypt('admin123'),
        ]);

        User::factory()->create([
            'name' => 'Пользователь АРМ',
            'email' => 'user@arm.local',
            'password' => bcrypt('user123'),
        ]);

        $this->call(ArchiveSeeder::class);
        
        $this->call(RolesAndPermissionsSeeder::class);
        
        $this->call(StagesSeeder::class);
    }
}
