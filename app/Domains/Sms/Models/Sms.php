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
        'received_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (Sms $sms) {
            $sms->fingerprint = static::fingerprintOf((string) $sms->body);
        });
    }

    /**
     * Bank SMS carry a balance or a time, so the same text is the same message,
     * whether it was pasted, synced, or sent twice by the bank.
     */
    public static function fingerprintOf(string $text): string
    {
        $text = preg_replace('/\s+/u', ' ', $text) ?? preg_replace('/\s+/', ' ', $text);

        return hash('sha256', trim($text));
    }

    /**
     * The stored message with this text, preferring one that made a transaction.
     */
    public static function findSameText(string $text): ?Sms
    {
        return static::query()
            ->where('fingerprint', static::fingerprintOf($text))
            ->orderByRaw('transaction_id is null')
            ->orderBy('id')
            ->first();
    }

    /**
     * Mark a message that was already stored when it came in again.
     */
    public function markAsAlreadyAdded(): static
    {
        $this->setAttribute('already_added', true);

        return $this;
    }

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

