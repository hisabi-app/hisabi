<?php

namespace App\Domains\Transaction\Models;

use App\Domains\Brand\Models\Brand;
use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Category;
use Carbon\Carbon;

class Transaction extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return TransactionFactory::new();
    }

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

    public function scopeSavings($query)
    {
        return $query->whereHas('brand.category', function ($query) {
            return $query->where('type', Category::SAVINGS);
        });
    }

    public function scopeInvestment($query)
    {
        return $query->whereHas('brand.category', function ($query) {
            return $query->where('type', Category::INVESTMENT);
        });
    }

    public static function tryCreateFromSms($sms)
    {
        $brandFromSms = $sms->meta['data']['brand'] ?? null;
        $amountFromSms = $sms->meta['data']['amount'] ?? null;
        $transactionDatetimeFromSMS = $sms->meta['data']['datetime'] ?? null;

        if(! $brandFromSms || ! $amountFromSms) {
            return;
        }

        $brand = Brand::findOrCreateNew($brandFromSms);

        $amount = (float) filter_var($amountFromSms, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $transactionDatetime = $transactionDatetimeFromSMS ? Carbon::parse($transactionDatetimeFromSMS) : now();

        return static::create([
            'amount' => $amount,
            'brand_id' => $brand->id,
            'created_at' => $transactionDatetime
        ]);
    }
}

