<?php

namespace App\Domains\Brand\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Domains\Transaction\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Category;
use Database\Factories\BrandFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

class Brand extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'aliases' => 'array',
    ];

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
        return static::findForSms($name) ?? static::create(['name' => $name]);
    }

    /**
     * The brand an SMS merchant belongs to: the one whose name, or one of its aliases,
     * appears in it. The longest match wins, so "LULU LAUNDRY CO" goes to Laundry rather
     * than Lulu; on a tie the oldest brand wins. An alias may use * for any text in
     * between, e.g. "LIFE * PHY" for "1485 LIFE 138 PHY".
     */
    public static function findForSms(string $name): ?self
    {
        $best = null;
        $bestLength = 0;

        foreach (static::orderBy('id')->get() as $brand) {
            foreach ([$brand->name, ...($brand->aliases ?? [])] as $candidate) {
                $length = static::matchLength((string) $candidate, $name);

                if ($length > $bestLength) {
                    $best = $brand;
                    $bestLength = $length;
                }
            }
        }

        return $best;
    }

    /**
     * How much of the SMS merchant a brand name or alias covers, 0 when it doesn't appear.
     */
    private static function matchLength(string $candidate, string $name): int
    {
        $parts = array_values(array_filter(array_map('trim', explode('*', $candidate)), fn ($part) => $part !== ''));

        if ($parts === []) {
            return 0;
        }

        $pattern = '/' . implode('.*', array_map(fn ($part) => preg_quote($part, '/'), $parts)) . '/iu';

        return preg_match($pattern, $name) ? array_sum(array_map('mb_strlen', $parts)) : 0;
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
