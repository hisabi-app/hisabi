<?php

namespace Database\Factories;

use App\Domains\Sms\Models\Sms;
use Illuminate\Database\Eloquent\Factories\Factory;

class SmsFactory extends Factory
{
    protected $model = Sms::class;
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
