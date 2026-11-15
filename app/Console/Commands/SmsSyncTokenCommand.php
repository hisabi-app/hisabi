<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class SmsSyncTokenCommand extends Command
{
    protected $signature = 'hisabi:sms-sync-token
        {name=mac : A name for the device that will send SMS}
        {--revoke : Remove the device\'s token instead of creating one}';

    protected $description = 'Create a token a device can use to send bank SMS to /api/v1/sms/sync';

    public function handle(): int
    {
        $user = User::query()->orderBy('id')->first();

        if (! $user) {
            $this->error('There is no user yet. Run php artisan hisabi:install first.');

            return self::FAILURE;
        }

        $name = $this->argument('name');

        // One token per device: a new one replaces the old
        $removed = $user->tokens()->where('name', $name)->delete();

        if ($this->option('revoke')) {
            $this->line($removed ? 'The token for "' . $name . '" no longer works.' : 'There was no token for "' . $name . '".');

            return self::SUCCESS;
        }

        $token = $user->createToken($name, ['sms:sync'])->plainTextToken;

        $this->line('Token for "' . $name . '" (shown once, keep it somewhere safe):');
        $this->newLine();
        $this->line($token);
        $this->newLine();

        if ($removed) {
            $this->line('The earlier token for "' . $name . '" no longer works.');
        }

        $this->line('On the Mac: hisabi-sms-sync set-token');

        return self::SUCCESS;
    }
}
