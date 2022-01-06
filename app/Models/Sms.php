<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sms extends Model
{
    use HasFactory;
    
    const INCOME = "INCOME";
    const EXPENSES = "EXPENSES";
    const UNKNOWN = "UNKNOWN";

    protected $guarded = [];

    protected $casts = [
        'meta' => 'array',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
