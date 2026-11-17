<?php

namespace App\Http\Commands\Kiosk\TranscribeKioskQuestionCommand;

use Illuminate\Http\UploadedFile;

readonly class TranscribeKioskQuestionCommand
{
    public function __construct(
        public UploadedFile $audio
    ) {}
}
