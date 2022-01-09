<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'meta' => 'array',
    ];
    
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function scopeExpenses($query) 
    {
        return $query->whereHas('category', function ($query) {
            return $query->where('type', Category::EXPENSES);
        });
    }

    public function scopeIncome($query) 
    {
        return $query->whereHas('category', function ($query) {
            return $query->where('type', Category::INCOME);
        });
    }
}
