<?php

namespace App\Domains\Brand\Models;

use App\Contracts\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Domains\Transaction\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Category;
use Database\Factories\BrandFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

class Brand extends Model implements Searchable
{
    use HasFactory;

    protected $guarded = [];

    protected static function newFactory(): Factory
    {
        return BrandFactory::new();
    }

    protected static function booted()
    {
        static::deleted(function ($brand) {
            $brand->transactions()->delete();
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function transactionsCount()
    {
        return $this->transactions()->count();
    }

    public static function findOrCreateNew($name)
    {
        foreach(static::get() as $knownBrand) {
            if(str_contains(strtolower($name), strtolower($knownBrand->name))) {
                return $knownBrand;
            }
        }

        return static::create(['name' => $name]);
    }

    public static function search($query): Builder
    {
        return (new static())->newQuery()
            ->where('name', 'LIKE', "%$query%")
            ->orWhereHas('category', function($builder) use($query) {
                return $builder->where('name', 'LIKE', "%$query%");
            });
    }
}
