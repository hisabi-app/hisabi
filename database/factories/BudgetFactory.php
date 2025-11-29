<?php

namespace Database\Factories;

use App\Domains\Budget\Models\Budget;
use Illuminate\Database\Eloquent\Factories\Factory;

class BudgetFactory extends Factory
{
    protected $model = Budget::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'amount' => $this->faker->numberBetween(100, 5000),
            'start_at' => now(),
            'reoccurrence' => Budget::DAILY,
            'period' => 1,
        ];
    }
}
