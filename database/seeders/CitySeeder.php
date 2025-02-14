<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\City;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = [
            ['name' => 'Rabat'],
            ['name' => 'Marrakech'],
            ['name' => 'Tanger'],
            ['name' => 'Paris'],
            ['name' => 'London'],
            ['name' => 'Berlin'],
            ['name' => 'Monaco'],
        ];

        foreach ($cities as $cityData) {
            City::create($cityData);
        }
    }
}
