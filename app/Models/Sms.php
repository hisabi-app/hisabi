<?php

namespace App\Models;

use App\Contracts\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sms extends Model implements Searchable
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'meta' => 'array',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * @param $query
     * @return Builder
     */
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
