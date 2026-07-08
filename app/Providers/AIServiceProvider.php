<?php

namespace App\Providers;

use App\Services\AI\AIParserInterface;
use App\Services\AI\MockAIParser;
use Illuminate\Support\ServiceProvider;

class AIServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // The only swap point for a real Claude-backed parser later:
        // set AI_PARSER_DRIVER=claude in .env and add that case here — no
        // controller/job code needs to change.
        $this->app->bind(AIParserInterface::class, function () {
            return match (config('services.ai.parser_driver')) {
                default => new MockAIParser(),
            };
        });
    }
}
