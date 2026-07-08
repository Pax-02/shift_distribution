<?php

use App\Services\AI\AIParserInterface;
use App\Services\AI\MockAIParser;

it('binds AIParserInterface to MockAIParser by default', function () {
    expect(app(AIParserInterface::class))->toBeInstanceOf(MockAIParser::class);
});
