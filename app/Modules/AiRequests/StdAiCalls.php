<?php

namespace Vanguard\Modules\AiRequests;

interface StdAiCalls
{
    public function __construct(string $api_key, string $model = '');

    public function textGeneration(string $clientQuestion, string $systemContext = ''): string;

    public function documentRecognition(string $prompt, string $imageContent, string $imageName): string;
}