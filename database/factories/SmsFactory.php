<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SmsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'body' => $this->faker->text(),
        ];
    }
}
