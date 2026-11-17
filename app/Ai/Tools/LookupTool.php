<?php

namespace App\Ai\Tools;

use App\Ai\AnswerCards;
use App\Domains\Assistant\Services\LookupService;
use App\Domains\Brand\Models\Brand;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Throwable;

/**
 * What the kiosk's lookup tools share: reading dates and names the way Claude passes them,
 * and answering in compact JSON. Results carry names and amounts only, never notes or SMS text.
 */
abstract class LookupTool implements Tool
{
    public function __construct(
        protected readonly LookupService $lookup,
        protected readonly AnswerCards $cards
    ) {}

    /**
     * @return array<string, mixed>
     */
    protected function dateSchema(JsonSchema $schema): array
    {
        return [
            'from' => $schema->string()->description('First day, YYYY-MM-DD.')->required(),
            'to' => $schema->string()->description('Last day, YYYY-MM-DD (counts in full). Days after today count up to today.')->required(),
        ];
    }

    /**
     * From the start of the first day to the end of the last, never past today.
     *
     * @return array{Carbon, Carbon}|string the range, or what was wrong with it as JSON
     */
    protected function range(Request $request): array|string
    {
        try {
            $from = Carbon::createFromFormat('Y-m-d', (string) $request['from'])->startOfDay();
            $to = Carbon::createFromFormat('Y-m-d', (string) $request['to'])->endOfDay();
        } catch (Throwable) {
            return $this->json(['error' => 'Dates must be YYYY-MM-DD.']);
        }

        $to = $to->min(now()->endOfDay());

        return $from->lte($to) ? [$from, $to] : $this->json(['error' => 'The first day is after the last one, or in the future.']);
    }

    /**
     * @return Category|string|null the category, an error (JSON) listing the closest names, or null when none was given
     */
    protected function category(Request $request): Category|string|null
    {
        if (blank($request['category'] ?? null)) {
            return null;
        }

        [$category, $closest] = $this->lookup->findCategory((string) $request['category']);

        return $category ?? $this->notFound('category', $request['category'], $closest);
    }

    /**
     * @return Brand|string|null the brand, an error (JSON) listing the closest names, or null when none was given
     */
    protected function brand(Request $request): Brand|string|null
    {
        if (blank($request['brand'] ?? null)) {
            return null;
        }

        [$brand, $closest] = $this->lookup->findBrand((string) $request['brand']);

        return $brand ?? $this->notFound('brand', $request['brand'], $closest);
    }

    protected function type(Request $request): string
    {
        $type = (string) ($request['type'] ?? 'expenses');

        return array_key_exists($type, LookupService::TYPES) ? $type : 'expenses';
    }

    protected function json(array $result): string
    {
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    private function notFound(string $kind, mixed $name, array $closest): string
    {
        return $this->json([
            'error' => "No {$kind} matches \"{$name}\".",
            'closest' => $closest,
        ]);
    }
}
