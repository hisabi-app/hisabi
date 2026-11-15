<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sms', function (Blueprint $table) {
            // The message's id on the device that sent it (the Messages GUID on a Mac), so a resent message is recognised
            $table->string('external_id')->nullable()->unique()->after('id');
            $table->string('sender')->nullable()->after('external_id');
            $table->dateTime('received_at')->nullable()->after('sender');
            // sha256 of the body with its whitespace collapsed: the same text pasted and synced is the same message.
            // Not unique: older data may hold the same message twice.
            $table->string('fingerprint', 64)->nullable()->index()->after('body');
        });

        DB::table('sms')->select(['id', 'body'])->orderBy('id')->chunkById(500, function ($rows) {
            foreach ($rows as $row) {
                $text = preg_replace('/\s+/u', ' ', (string) $row->body) ?? preg_replace('/\s+/', ' ', (string) $row->body);

                DB::table('sms')->where('id', $row->id)->update(['fingerprint' => hash('sha256', trim($text))]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('sms', function (Blueprint $table) {
            $table->dropUnique(['external_id']);
            $table->dropIndex(['fingerprint']);
            $table->dropColumn(['external_id', 'sender', 'received_at', 'fingerprint']);
        });
    }
};
