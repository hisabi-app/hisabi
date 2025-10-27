<?php

namespace Database\Factories;

use App\Models\Category;
use App\Domains\Brand\Models\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;

class BrandFactory extends Factory
{
    protected $model = Brand::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'category_id' => Category::factory(),
        ];
    }
}
