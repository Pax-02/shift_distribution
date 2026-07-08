<?php

namespace App\Services\AI;

use Illuminate\Support\Carbon;

/**
 * Deterministic, network-free stand-in for a real LLM-backed parser.
 * Extracts fields with plain regex/keyword heuristics rather than always
 * returning a hardcoded happy-path payload, so it exercises the same
 * "some fields missing/unparseable" path that a real parser would produce
 * on an ambiguous message — which is what ParsedMessageValidator needs to
 * be tested against.
 */
class MockAIParser implements AIParserInterface
{
    private const MODEL_ID = 'mock-v1';

    public function modelIdentifier(): string
    {
        return self::MODEL_ID;
    }

    public function parseMessage(string $message): ParsedShiftMessageDTO
    {
        $client = $this->extractClient($message);
        $date = $this->extractDate($message);
        [$pickup, $destination] = $this->extractLocations($message);
        [$startTime, $endTime] = $this->extractTimeRange($message);
        $genderRequirement = $this->extractGenderRequirement($message);

        $requiredFields = [$client, $date, $pickup, $destination, $startTime, $endTime];
        $confidence = count(array_filter($requiredFields, fn ($f) => $f !== null)) / count($requiredFields);

        return new ParsedShiftMessageDTO(
            client: $client,
            date: $date,
            pickup: $pickup,
            destination: $destination,
            startTime: $startTime,
            endTime: $endTime,
            genderRequirement: $genderRequirement,
            confidence: round($confidence, 2),
            raw: $message,
        );
    }

    /** "... for Jane Doe ..." -> "Jane Doe" */
    private function extractClient(string $message): ?string
    {
        if (! preg_match('/\bfor\s+([A-Z][a-zA-Z\'-]+(?:\s+[A-Z][a-zA-Z\'-]+){0,2})/', $message, $matches)) {
            return null;
        }

        return trim($matches[1]);
    }

    /** "12/08", "12/08/2026", "12-08-26" -> "2026-08-12" (rolls forward a year if the bare day/month has already passed). */
    private function extractDate(string $message): ?string
    {
        if (! preg_match('/\b(\d{1,2})[\/\-](\d{1,2})(?:[\/\-](\d{2,4}))?\b/', $message, $matches)) {
            return null;
        }

        $day = (int) $matches[1];
        $month = (int) $matches[2];
        $yearGiven = isset($matches[3]);
        $year = $yearGiven ? (int) $matches[3] : (int) now()->format('Y');
        $year += ($year < 100) ? 2000 : 0;

        if ($month < 1 || $month > 12 || $day < 1 || $day > 31) {
            return null;
        }

        $date = Carbon::create($year, $month, $day);

        if ($date === false) {
            return null;
        }

        if (! $yearGiven && $date->isPast()) {
            $date->addYear();
        }

        return $date->toDateString();
    }

    /** @return array{0: ?string, 1: ?string} [start_time, end_time] as "H:i", from the first two HH:MM matches. */
    private function extractTimeRange(string $message): array
    {
        preg_match_all('/\b([01]?\d|2[0-3]):([0-5]\d)\b/', $message, $matches, PREG_SET_ORDER);

        $normalize = fn (array $match) => sprintf('%02d:%s', (int) $match[1], $match[2]);

        return [
            isset($matches[0]) ? $normalize($matches[0]) : null,
            isset($matches[1]) ? $normalize($matches[1]) : null,
        ];
    }

    /** @return array{0: ?string, 1: ?string} [pickup, destination] */
    private function extractLocations(string $message): array
    {
        $pickup = null;
        $destination = null;

        if (preg_match('/pickup\s*[:\-]?\s*([^,\.]+)/i', $message, $matches)) {
            $pickup = trim($matches[1]);
        }

        if (preg_match('/(?:dropoff|drop-off|destination)\s*[:\-]?\s*([^,\.]+)/i', $message, $matches)) {
            $destination = trim($matches[1]);
        }

        return [$pickup, $destination];
    }

    private function extractGenderRequirement(string $message): string
    {
        return match (true) {
            (bool) preg_match('/\bfemale\b/i', $message) => 'female',
            (bool) preg_match('/\bmale\b/i', $message) => 'male',
            default => 'any',
        };
    }
}
