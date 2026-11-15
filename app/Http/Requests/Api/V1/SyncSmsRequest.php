<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class SyncSmsRequest extends FormRequest
{
    public const MAX_MESSAGES = 100;

    public function authorize(): bool
    {
        // Only a token made for syncing: tokenCan() would also pass an unscoped (*) token
        $abilities = $this->user()?->currentAccessToken()?->abilities ?? [];

        return in_array('sms:sync', $abilities, true);
    }

    public function rules(): array
    {
        return [
            'messages' => 'required|array|min:1|max:' . self::MAX_MESSAGES,
            'messages.*.guid' => 'required|string|max:255',
            'messages.*.sender' => 'nullable|string|max:255',
            'messages.*.received_at' => 'required|date',
            'messages.*.text' => 'required|string|max:10000',
        ];
    }
}
