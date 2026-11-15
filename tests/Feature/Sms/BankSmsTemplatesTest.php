<?php

namespace Tests\Feature\Sms;

use App\Contracts\SmsTransactionProcessor;
use App\Domains\Brand\Models\Brand;
use App\Domains\Sms\Models\Sms;
use App\Domains\Transaction\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The bank formats in config/hisabi.php, with made-up merchants, amounts and card numbers.
 */
class BankSmsTemplatesTest extends TestCase
{
    use RefreshDatabase;

    public function test_nbd_debit_card_purchase()
    {
        $transaction = $this->transactionFrom('Purchase of AED 47.35 with Debit Card ending 4821 at CORNER BAKERY LL, DUBAI. Avl Balance is AED 3,210.40.');

        $this->assertEquals('CORNER BAKERY LL', $transaction->brand->name);
        $this->assertEquals(47.35, $transaction->amount);
    }

    public function test_nbd_funds_transfer_with_debit_card()
    {
        $transaction = $this->transactionFrom('Funds Transfer of AED 2,340.00 has been made using your Debit Card ending 4821 at SAMPLE EXCHANGE. Your current balance is AED 880.15.');

        $this->assertEquals('SAMPLE EXCHANGE', $transaction->brand->name);
        $this->assertEquals(2340, $transaction->amount);
    }

    public function test_adcb_credit_card_in_aed()
    {
        $transaction = $this->transactionFrom('Credit Card XX9076 was used for AED310.00 on 12/03/2026 18:42:05 at Sunrise Telecom (S, 800111-AE. Available limit  AED8400.00');

        $this->assertEquals('Sunrise Telecom (S', $transaction->brand->name);
        $this->assertEquals(310, $transaction->amount);
        $this->assertEquals('2026-03-12', $transaction->created_at->format('Y-m-d'));
    }

    public function test_adcb_credit_card_in_aed_with_foreign_transaction_fee()
    {
        $transaction = $this->transactionFrom('Credit Card XXX9076 used for AED18.49 (+2.99% foreign txn fee) on 21/03/2026 07:15:33 at STREAMBOX*PREMIUM,SAN JOSE-US. Avl. Cr.limit AED8381.51');

        $this->assertEquals('STREAMBOX*PREMIUM', $transaction->brand->name);
        $this->assertEquals(18.49, $transaction->amount);
        $this->assertEquals('2026-03-21', $transaction->created_at->format('Y-m-d'));
    }

    public function test_emirates_islamic_credit_card_purchase_spanning_several_lines()
    {
        $sms = "Credit Card Purchase\nCard Ending: 3307\nAt: Example Store, Dubai\nAmount: AED 61.85\nDate: 08/04/2026, 14:05\nAvailable Limit: AED 9,935.80";

        $transaction = $this->transactionFrom($sms);

        $this->assertEquals('Example Store', $transaction->brand->name);
        $this->assertEquals(61.85, $transaction->amount);
        $this->assertEquals('2026-04-08', $transaction->created_at->format('Y-m-d'));
        $this->assertEquals($sms, Sms::sole()->body);
    }

    public function test_dib_online_purchase()
    {
        $transaction = $this->transactionFrom('Online Purchase of AED 245.30 from a/c no. 002-100-4567***-02 on 16-APR-2026 09:12 from Example Pay, 800 12345. Available balance is AED 5,120.00');

        $this->assertEquals('Example Pay', $transaction->brand->name);
        $this->assertEquals(245.3, $transaction->amount);
        $this->assertEquals('2026-04-16', $transaction->created_at->format('Y-m-d'));
    }

    public function test_dib_salary_uses_the_existing_salary_brand()
    {
        $salary = Brand::factory()->create(['name' => 'Salary']);

        $transaction = $this->transactionFrom('Salary amount of AED  18,750.00 credited to your A/C no. 002-100-4567***-02 on 25-APR-2026. Available balance is  23,870.00');

        $this->assertTrue($transaction->brand->is($salary));
        $this->assertEquals(18750, $transaction->amount);
        $this->assertEquals('2026-04-25', $transaction->created_at->format('Y-m-d'));
        $this->assertEquals(1, Brand::count());
    }

    public function test_dib_finance_repayment()
    {
        $transaction = $this->transactionFrom('AED 3,200.00 has been drawn from A/C No. 002-100-456XXXX-02 as Finance Repayment. Available balance is  20,670.00');

        $this->assertEquals('Finance Repayment', $transaction->brand->name);
        $this->assertEquals(3200, $transaction->amount);
    }

    public function test_messages_that_are_not_spending_or_income_create_no_transaction()
    {
        $messages = [
            'foreign currency on a credit card' => 'Credit Card XX9076 was used for USD17.40 on 12/03/2026 10:00:00 at SAMPLE HOSTING, +14155550100-US. Available limit  AED8000.00',
            'foreign currency on a debit card' => 'Purchase of USD 3.65 with Debit Card ending 4821 at CLOUD SERVICES, seattle.example. Avl Balance is AED 3,000.00.  Pls refer stmt for exact amt.',
            'foreign currency on a multi-line message' => "Credit Card Purchase\nCard Ending: 3307\nAt: Example Hosting, Dublin\nAmount: USD 14.70\nDate: 08/04/2026, 14:05\nAvailable Limit: AED 9,890.00",
            'reversal' => 'An amount of USD6.35 has been reversed to your Credit Card XXX9076 on 12/03/2026 10:00:00 by SAMPLE INC.,+14155550100-US.',
            'refund' => 'Dear Customer, Amount of AED 61.85 from Example Store has been credited to your card ending 3307. Available Limit is AED 9,871.85.',
            'card payment' => 'Your payment of AED 850 against Credit Card no. XXX9076 was received at 10:15 AM on 12/03/2026. Thank you.',
            'card payment confirmation' => 'This is to confirm receipt of your payment of AED 430.00 towards your Credit Card starting with 123456  on 12/03/2026. Available limit is   AED 9,500.00.',
            'own account transfer out' => 'AED 1,850.00 has been drawn from A/C No. 002-100-456XXXX-02 as OWN ACCOUNT TRNSFER. Available balance is  18,670.00',
            'instant transfer in' => 'Dear Customer, AED 1,850.00 has been credited to your account 012XXX34XXX56 towards instant transfer. The available balance is AED 4,760.00.',
            'telegraphic transfer' => 'AED 750.00 has been deducted from your account  012XXX34XXX56 for issuance of Telegraphic Transfer. The available balance is AED 4,250.00.',
            'direct debit' => 'AED 1,200.00 has been debited from your account no. 012XXX34XXX56 DDR-0001XXX02XXX-03. The available balance is AED 3,050.00.',
            'cash withdrawal' => 'Cash Withdrawal of AED 430.00 with Debit Card ending 4821 at SAMPLE BANK ATM, DUBAI. Avl Bal is AED 2,550.00.',
            'FAB account credit' => 'An amount of AED 6.40 has been credited to your FAB account XXXX1234 on 30/04/2026 .Your balance is AED 10,006.40',
            'FAB remittance' => "Inward Remittance \nAccount XXXX1234 \nDate 30/04/2026 \nCredit  \nAED 430.00\nBalance AED 10,430.00",
            'declined' => 'Transaction of AED 120.00 on your Card 4821 at shop.example.com was declined due to insufficient limit. For details call 600000000',
            'billing alert' => 'Cr.Card XXX9076 Billing alert: Pay AED1200.00 to avoid fin.charges; Tot. billed: AED4760.00; Pay min. AED250.00 by Nov 25 2026 to avoid AED83.40 late fees.',
            'statement reminder' => "Reminder for Credit Card Payment\nCard Starting: 123456\nDue Date: 25/04/2026\nMinimum Amount Due: AED 250.00\nAmount to be paid to avoid charges: AED 1,200.00\nPlease pay on or before due date to avoid commitment to donate amount.",
            'OTP' => 'Dear Customer, to complete adding your card ending 4821 to the digital wallet, retrieve the OTP from the app. Do not share your OTP !',
        ];

        foreach ($messages as $kind => $sms) {
            app(SmsTransactionProcessor::class)->process($sms);

            $this->assertEquals(0, Transaction::count(), "A {$kind} message created a transaction");
        }
    }

    private function transactionFrom(string $sms): Transaction
    {
        app(SmsTransactionProcessor::class)->process($sms);

        $transaction = Sms::sole()->transaction;
        $this->assertNotNull($transaction, "No transaction was created from: {$sms}");

        return $transaction;
    }
}
