<?php

namespace App\Domains\Sms\Models;

use App\Contracts\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Domains\Transaction\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\SmsFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

class Sms extends Model implements Searchable
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'meta' => 'array',
    ];

    protected static function newFactory(): Factory
    {
        return SmsFactory::new();
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public static function search($query): Builder
    {
        return (new static())->newQuery()
            ->where('body', 'LIKE', "%$query%");
    }

    public function setDefaultDateIfNotFound($defaultDate = null): void
    {
        if(! isset($this->meta['data']['datetime']) && $defaultDate !== null && ! empty($defaultDate)) {
            $this->meta = array_merge_recursive($this->meta, [
                'data' => [
                    'datetime' => $defaultDate,
                ],
            ]);
        }
    }
}

