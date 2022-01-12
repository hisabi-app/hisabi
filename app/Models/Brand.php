<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Brand extends Model
{
    use HasFactory;
    
    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public static function findOrCreateNew($name)
    {
        // TODO: find better solution for detecting brands maybe using SQL query?
        foreach(static::get() as $knownBrand) {
            if(str_contains(strtolower($name), strtolower($knownBrand->name))) {
                return $knownBrand;
            }
        }

        return static::create(['name' => $name]);
    }
}
