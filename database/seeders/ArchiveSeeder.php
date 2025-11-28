<?php

namespace Database\Seeders;

use App\Models\Archive;
use Illuminate\Database\Seeder;

class ArchiveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $archiveData = [
            '2023-2024' => [
                [
                    'code' => '2.2.02.12',
                    'name' => 'Разработка СП "Проектирование конструкций"',
                    'total_cost' => 200000,
                    'cost_2023' => 100000,
                    'cost_2024' => 100000,
                    'start_date' => 'март 2023',
                    'end_date' => 'апрель 2024',
                    'organization' => 'РУП "СТРОЙТЕХНОРМ" отдел 18',
                    'type' => 'Впервые',
                    'year_period' => '2023-2024'
                ],
                [
                    'code' => '2.2.02.13',
                    'name' => 'Разработка СП "Строительные материалы"',
                    'total_cost' => 150000,
                    'cost_2023' => 75000,
                    'cost_2024' => 75000,
                    'start_date' => 'январь 2023',
                    'end_date' => 'декабрь 2024',
                    'organization' => 'РУП "СТРОЙТЕХНОРМ" отдел 19',
                    'type' => 'Взамен',
                    'year_period' => '2023-2024'
                ],
                [
                    'code' => '2.2.02.14',
                    'name' => 'Разработка СП "Инженерные системы"',
                    'total_cost' => 180000,
                    'cost_2023' => 90000,
                    'cost_2024' => 90000,
                    'start_date' => 'февраль 2023',
                    'end_date' => 'март 2024',
                    'organization' => 'РУП "СТРОЙТЕХНОРМ" отдел 20',
                    'type' => 'Впервые',
                    'year_period' => '2023-2024'
                ]
            ],
            '2022-2023' => [
                [
                    'code' => '2.2.01.12',
                    'name' => 'Разработка СП "Архитектурные решения"',
                    'total_cost' => 160000,
                    'cost_2023' => 80000,
                    'cost_2024' => 80000,
                    'start_date' => 'март 2022',
                    'end_date' => 'апрель 2023',
                    'organization' => 'РУП "СТРОЙТЕХНОРМ" отдел 18',
                    'type' => 'Впервые',
                    'year_period' => '2022-2023'
                ],
                [
                    'code' => '2.2.01.13',
                    'name' => 'Разработка СП "Конструктивные решения"',
                    'total_cost' => 140000,
                    'cost_2023' => 70000,
                    'cost_2024' => 70000,
                    'start_date' => 'январь 2022',
                    'end_date' => 'декабрь 2023',
                    'organization' => 'РУП "СТРОЙТЕХНОРМ" отдел 19',
                    'type' => 'Взамен',
                    'year_period' => '2022-2023'
                ]
            ],
            '2021-2022' => [
                [
                    'code' => '2.2.00.12',
                    'name' => 'Разработка СП "Проектирование зданий"',
                    'total_cost' => 120000,
                    'cost_2023' => 60000,
                    'cost_2024' => 60000,
                    'start_date' => 'март 2021',
                    'end_date' => 'апрель 2022',
                    'organization' => 'РУП "СТРОЙТЕХНОРМ" отдел 18',
                    'type' => 'Впервые',
                    'year_period' => '2021-2022'
                ],
                [
                    'code' => '2.2.00.13',
                    'name' => 'Разработка СП "Строительные технологии"',
                    'total_cost' => 100000,
                    'cost_2023' => 50000,
                    'cost_2024' => 50000,
                    'start_date' => 'январь 2021',
                    'end_date' => 'декабрь 2022',
                    'organization' => 'РУП "СТРОЙТЕХНОРМ" отдел 19',
                    'type' => 'Взамен',
                    'year_period' => '2021-2022'
                ]
            ],
            '2020-2021' => [
                [
                    'code' => '2.1.99.12',
                    'name' => 'Разработка СП "Строительные нормы"',
                    'total_cost' => 80000,
                    'cost_2023' => 40000,
                    'cost_2024' => 40000,
                    'start_date' => 'март 2020',
                    'end_date' => 'апрель 2021',
                    'organization' => 'РУП "СТРОЙТЕХНОРМ" отдел 18',
                    'type' => 'Впервые',
                    'year_period' => '2020-2021'
                ]
            ],
            '2019-2020' => [
                [
                    'code' => '2.1.98.12',
                    'name' => 'Разработка СП "Технические требования"',
                    'total_cost' => 60000,
                    'cost_2023' => 30000,
                    'cost_2024' => 30000,
                    'start_date' => 'март 2019',
                    'end_date' => 'апрель 2020',
                    'organization' => 'РУП "СТРОЙТЕХНОРМ" отдел 18',
                    'type' => 'Впервые',
                    'year_period' => '2019-2020'
                ]
            ]
        ];

        foreach ($archiveData as $yearPeriod => $records) {
            foreach ($records as $record) {
                Archive::create($record);
            }
        }
    }
}