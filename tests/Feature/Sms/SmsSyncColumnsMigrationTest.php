<?php

namespace Tests\Feature\Sms;

use App\Domains\Sms\Models\Sms;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SmsSyncColumnsMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_fingerprints_the_messages_already_stored(): void
    {
        $migration = require database_path('migrations/2026_11_15_000000_add_sync_columns_to_sms_table.php');

        $migration->down();
        $this->assertFalse(Schema::hasColumn('sms', 'fingerprint'));

        DB::table('sms')->insert([
            ['body' => "Purchase of AED 12.00 with Debit Card ending 4821 at SAMPLE CAFE,\n "],
            ['body' => 'Purchase of AED 12.00  with Debit Card ending 4821 at SAMPLE CAFE,'],
        ]);

        $migration->up();

        $expected = Sms::fingerprintOf('Purchase of AED 12.00 with Debit Card ending 4821 at SAMPLE CAFE,');
        $this->assertEquals([$expected, $expected], DB::table('sms')->orderBy('id')->pluck('fingerprint')->all());
    }

    public function test_saving_a_message_keeps_its_fingerprint_current(): void
    {
        $sms = Sms::create(['body' => 'first text']);
        $this->assertEquals(Sms::fingerprintOf('first text'), $sms->fingerprint);

        $sms->update(['body' => 'second   text']);
        $this->assertEquals(Sms::fingerprintOf('second text'), $sms->fresh()->fingerprint);
    }
}
