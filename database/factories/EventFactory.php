<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Event;
use App\Models\User;
use App\Models\City;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{

    protected $model = Event::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {



        $cityId = City::inRandomOrder()->first()->id;
        $creatorId = User::inRandomOrder()->first()->id;

        $capacity = $this->faker->numberBetween(10, 1000);

        $bookedSeats = $this->faker->numberBetween(0, $capacity);

        $seatsAvailable = $capacity - $bookedSeats;

        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(2),
            'start_time' => $this->faker->dateTimeBetween('+1 day', '+1 week'),
            'end_time' => $this->faker->dateTimeBetween('+2 week', '+3 weeks'),
            'location' => $this->faker->address(),
            'capacity' => $capacity,
            'seats_available' => $seatsAvailable,
            'booked_seats' => $bookedSeats,
            'city_id' => $cityId,
            'creator_id' => $creatorId,
        ];
    }
}
