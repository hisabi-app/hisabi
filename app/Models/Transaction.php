<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'meta' => 'array',
    ];
    
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function scopeExpenses($query) 
    {
        return $query->whereHas('brand.category', function ($query) {
            return $query->where('type', Category::EXPENSES);
        });
    }

    public function scopeIncome($query) 
    {
        return $query->whereHas('brand.category', function ($query) {
            return $query->where('type', Category::INCOME);
        });
    }

    public function statistics2() {
        return Transaction::query()
            ->income()
            ->with(['brand', 'brand.category'])
            ->join('brands', 'brands.id', '=', 'transactions.brand_id')
            // ->join('categories', 'categories.id', '=', 'brands.category_id')
            ->select(DB::raw("brands.category_id, SUM(transactions.amount) as total"))
            ->groupBy("brands.category_id");
    }
}
