<?php

namespace App\Models;

use App\Contracts\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model implements Searchable
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

    /**
     * @param $query
     * @return Builder
     */
    public static function search($query): Builder
    {
        return (new static())->newQuery()
            ->where('name', 'LIKE', "%$query%");
    }
}
