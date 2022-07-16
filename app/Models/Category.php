<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    const INCOME = "INCOME";
    const EXPENSES = "EXPENSES";
    const SAVINGS = "SAVINGS";
    const INVESTMENT = "INVESTMENT";

    protected $guarded = [];

    protected static function booted()
    {
        static::deleted(function ($category) {
            $category->brands->each->delete();
        });
    }

    public function brands()
    {
        return $this->hasMany(Brand::class);
    }
}
