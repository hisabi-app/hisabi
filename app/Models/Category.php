<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    const INCOME = "INCOME";
    const EXPENSES = "EXPENSES";

    protected $guarded = [];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function brands()
    {
        return $this->hasMany(Brand::class);
    }
}
