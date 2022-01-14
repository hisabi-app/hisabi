<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategoryBrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Category::create(['name' => 'Salary', 'type' => Category::INCOME])
            ->brands()
            ->create(['name' => 'Salary']);

        Category::create(['name' => 'Housing', 'type' => Category::EXPENSES])
            ->brands()
            ->create(['name' => 'Housing']);

        Category::create(['name' => 'Groceries', 'type' => Category::EXPENSES])
            ->brands()
            ->createMany([
                ['name' => 'LULU'],
                ['name' => 'ASWAAQ'],
                ['name' => 'TUDO'],
                ['name' => 'CARREFOUR'],
            ]);

        Category::create(['name' => 'Utilities', 'type' => Category::EXPENSES])
            ->brands()
            ->createMany([
                ['name' => 'ETISALAT'],
                ['name' => 'Smart Dubai'],
            ]);

        Category::create(['name' => 'Transportation', 'type' => Category::EXPENSES])
            ->brands()
            ->createMany([
                ['name' => 'EMARAT'],
                ['name' => 'Careem'],
                ['name' => 'ENOC'],
            ]);

        Category::create(['name' => 'Shopping', 'type' => Category::EXPENSES])
            ->brands()
            ->createMany([
                ['name' => 'CHOCOLALA'],
                ['name' => 'IKEA'],
                ['name' => 'HOME CENTRE'],
                ['name' => 'MCDONALDS'],
                ['name' => 'SUBWAY'],
            ]);

        Category::create(['name' => 'Internet Subscription', 'type' => Category::EXPENSES])
            ->brands()
            ->createMany([
                ['name' => 'FACEBK'],
                ['name' => 'Google'],
            ]);

        Category::create(['name' => 'Family Support', 'type' => Category::EXPENSES])
            ->brands()
            ->createMany([
                ['name' => 'Family Support'],
            ]);

        Category::create(['name' => 'Debt', 'type' => Category::EXPENSES])
            ->brands()
            ->createMany([
                ['name' => 'Debt'],
            ]);

        Category::create(['name' => 'Medicine', 'type' => Category::EXPENSES])
            ->brands()
            ->createMany([
                ['name' => 'MEDICINA'],
                ['name' => 'LIFE PHY'],
                ['name' => 'IBN SINA'],
            ]);
            
        Category::create(['name' => 'Others', 'type' => Category::EXPENSES])
            ->brands()
            ->createMany([
                ['name' => 'Others'],
            ]);
    }
}
