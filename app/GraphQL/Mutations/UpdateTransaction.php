<?php

namespace App\GraphQL\Mutations;

use App\Models\Brand;
use App\Models\Transaction;

class UpdateTransaction
{
    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function __invoke($_, array $args)
    {
        $id = $args['id'];
        $amount = $args['amount'];
        $brand = $args['brand'];

        $transaction = Transaction::findOrFail($id);

        $transaction->update([
            'amount' => $amount,
            'brand_id' => $brand,
            'category_id' => Brand::findOrFail($brand)->category_id
        ]);

        return $transaction->fresh();
    }
}
