<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class TranscribeKioskQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // What tablets record: webm/opus (Chrome), mp4/aac (Safari); a webm with no video reads as video/webm
            'audio' => 'required|file|max:5120|mimetypes:audio/webm,video/webm,audio/mp4,video/mp4,audio/x-m4a,audio/aac,audio/mpeg,audio/ogg,audio/wav,audio/x-wav',
        ];
    }
}
