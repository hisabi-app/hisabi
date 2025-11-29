<?php

namespace App\Domains\Category\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Domains\Brand\Models\Brand;
use App\Domains\Transaction\Models\Transaction;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

class Category extends Model
{
    use HasFactory;

    const INCOME = "INCOME";
    const EXPENSES = "EXPENSES";
    const SAVINGS = "SAVINGS";
    const INVESTMENT = "INVESTMENT";

    protected $guarded = [];

    protected static function newFactory(): Factory
    {
        return CategoryFactory::new();
    }

    protected static function booted(): void
    {
        static::deleted(function ($category) {
            $category->brands->each->delete();
        });
    }

    public function brands(): HasMany
    {
        return $this->hasMany(Brand::class);
    }

    public function transactions(): HasManyThrough
    {
        return $this->hasManyThrough(Transaction::class, Brand::class);
    }
}
