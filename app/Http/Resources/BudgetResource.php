<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BudgetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'amount' => $this->amount,
            'total_spent_percentage' => $this->total_spent_percentage,
            'start_at_date' => $this->start_at_date,
            'end_at_date' => $this->end_at_date,
            'remaining_to_spend' => $this->remaining_to_spend,
            'total_margin_per_day' => $this->total_margin_per_day,
            'remaining_days' => $this->remaining_days,
            'elapsed_days_percentage' => $this->elapsed_days_percentage,
            'is_saving' => $this->is_saving,
            'total_transactions_amount' => $this->total_transactions_amount,
        ];
    }
}
