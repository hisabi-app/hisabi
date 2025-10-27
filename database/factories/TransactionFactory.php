<?php

namespace Database\Factories;

use App\Domains\Brand\Models\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Domains\Transaction\Models\Transaction;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'amount' => $this->faker->numberBetween(),
            'brand_id' => Brand::factory(),
            'note' => $this->faker->text()
        ];
    }
}
