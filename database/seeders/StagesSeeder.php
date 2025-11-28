<?php

namespace Database\Seeders;

use App\Models\Stage;
use Illuminate\Database\Seeder;

class StagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stages = [
            'ТЗ',
            'ПР',
            'ОР',
            'НТЭ',
            'С',
            'ИР',
            'МВС',
            'ВЭ',
            'У',
            'НЦЗПИ',
            'И',
            'ПЕР',
            'БелГИСС',
            'Э1',
            'Э2',
            'Э3',
            'Э4',
            'Э5',
            'Э6',
            'Э7',
            'Э8',
            'Э9',
            'Э10',
        ];

        foreach ($stages as $stageName) {
            Stage::firstOrCreate(['name' => $stageName]);
        }
    }
}

