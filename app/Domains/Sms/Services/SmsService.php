<?php

namespace App\Domains\Sms\Services;

use App\Domains\Sms\Models\Sms;
use App\Contracts\SmsTransactionProcessor;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SmsService
{
    public const CREATED = 'created';
    public const DUPLICATE = 'duplicate';

    public function __construct(
        private readonly SmsTransactionProcessor $smsTransactionProcessor
    ) {}

    public function getPaginated(int $perPage = 100): LengthAwarePaginator
    {
        return Sms::orderBy('transaction_id')->paginate($perPage);
    }

    /**
     * Pasted text: each message in it is read once. A message that's already stored
     * (pasted before, or sent by the Mac) comes back marked as already added.
     */
    public function create(array $data): Collection
    {
        $createdAt = $data['created_at'] ?? null;
        $messages = $this->smsTransactionProcessor->split($data['body']);

        // One message the Mac sent whole, pasted again with its line breaks
        if (count($messages) > 1 && $existing = Sms::findSameText($data['body'])) {
            return collect([$this->readAgain($existing, $createdAt)]);
        }

        return collect($messages)->map(function (string $message) use ($createdAt) {
            $existing = Sms::findSameText($message);

            return $existing
                ? $this->readAgain($existing, $createdAt)
                : $this->smsTransactionProcessor->processMessage($message, $createdAt);
        });
    }

    /**
     * One message sent by a device (the Mac reading Messages), read as a whole.
     *
     * @param array{guid: string, sender: ?string, received_at: string, text: string} $message
     * @return array{guid: string, status: string, transaction_id: ?int}
     */
    public function sync(array $message): array
    {
        $guid = $message['guid'];
        $body = implode("\n", $this->smsTransactionProcessor->split($message['text']));

        $existing = Sms::where('external_id', $guid)->first() ?? $this->findSameMessage($body);

        if ($existing) {
            return $this->result($guid, self::DUPLICATE, $existing->transaction_id);
        }

        $receivedAt = Carbon::parse($message['received_at'])->setTimezone(config('app.timezone'));

        try {
            // A savepoint, so a message that loses a race takes its transaction with it
            $sms = DB::transaction(fn () => $this->smsTransactionProcessor->processMessage(
                $body,
                // Messages without their own date (e.g. NBD) are dated when they arrived
                $receivedAt->format('Y-m-d H:i:s'),
                [
                    'external_id' => $guid,
                    'sender' => $message['sender'] ?? null,
                    'received_at' => $receivedAt,
                ],
            ));
        } catch (UniqueConstraintViolationException $e) {
            // The same message sent twice at once: the other request stored it
            if (! str_contains($e->getMessage(), 'external_id')) {
                throw $e;
            }

            return $this->result($guid, self::DUPLICATE, null);
        }

        return $this->result($guid, self::CREATED, $sms->transaction_id);
    }

    public function update(int $id, array $data): Sms
    {
        $sms = Sms::findOrFail($id);
        $sms->update($data);
        return $sms->fresh();
    }

    public function delete(int $id): Sms
    {
        $sms = Sms::findOrFail($id);
        $sms->delete();
        return $sms;
    }

    /**
     * A message is already stored if its text is, or, when it spans several lines,
     * if every line of it was pasted before.
     */
    private function findSameMessage(string $body): ?Sms
    {
        if ($existing = Sms::findSameText($body)) {
            return $existing;
        }

        $parts = $this->smsTransactionProcessor->split($body);

        if (count($parts) < 2) {
            return null;
        }

        $stored = collect($parts)->map(fn (string $part) => Sms::findSameText($part));

        if ($stored->contains(null)) {
            return null;
        }

        return $stored->first(fn (Sms $sms) => $sms->transaction_id !== null) ?? $stored->first();
    }

    /**
     * A message that's already stored. One that made no transaction is read again
     * (a template for it may have been added since) instead of being stored twice.
     */
    private function readAgain(Sms $sms, ?string $createdAt): Sms
    {
        if ($sms->transaction_id === null) {
            $sms = $this->smsTransactionProcessor->processMessage(
                $sms,
                $sms->received_at?->format('Y-m-d H:i:s') ?? $createdAt
            );

            if ($sms->transaction_id !== null) {
                return $sms;
            }
        }

        return $sms->markAsAlreadyAdded();
    }

    private function result(string $guid, string $status, ?int $transactionId): array
    {
        return [
            'guid' => $guid,
            'status' => $status,
            'transaction_id' => $transactionId,
        ];
    }
}
