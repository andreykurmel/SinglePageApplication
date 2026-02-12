<?php

namespace Vanguard\Modules\AiRequests;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Str;

class OpenAiApi implements StdAiCalls
{
    /**
     * @var string
     */
    protected $api_key;

    /**
     * @var string
     */
    protected $model;

    /**
     * @param string $api_key
     * @param string $model
     */
    public function __construct(string $api_key, string $model = 'gpt-4o-mini')
    {
        $this->api_key = $api_key;
        $this->model = $model;
    }

    /**
     * @param string $prompt
     * @param string $imageContent
     * @param string $imageName
     * @return string
     */
    public function documentRecognition(string $prompt, string $imageContent, string $imageName): string
    {
        $messages = [];
        $messages[] = [
            'role' => 'user',
            'content' => [
                [
                    'type' => 'text',
                    'text' => $prompt
                ],
                [
                    'type' => 'image_url',
                    'image_url' => [
                        'url' => base64_encode($imageContent)
                    ]
                ]
            ],
        ];
        return $this->chatCompletionsCall($messages);
    }

    /**
     * @param string $clientQuestion
     * @param string $systemContext
     * @return string
     */
    public function textGeneration(string $clientQuestion, string $systemContext = ''): string
    {
        $messages = [
            ['role' => 'user', 'content' => $clientQuestion],
        ];
        if ($systemContext) {
            $messages[] = ['role' => 'system', 'content' => $systemContext];
        }
        return $this->chatCompletionsCall($messages);
    }

    /**
     * @param array $messages
     * @return string
     */
    public function chatCompletionsCall(array $messages): string
    {
        $curl = new Client();
        try {
            $response = $curl->post($this->baseUrl('chat/completions'), [
                'headers' => $this->headers(),
                'json' => [
                    'model' => strtolower($this->model),
                    'messages' => $messages,
                ],
            ]);
        } catch (Exception $e) {
            if (Str::contains($e->getMessage(), 'You exceeded your current quota, please check your plan and billing details.')) {
                return 'OpenAI: You exceeded your current quota, please check your plan and billing details.';
            } else {
                return $e->getMessage();
            }
        }
        $res = json_decode($response->getBody()->getContents(), true);
        return $this->collectChatCompletionMessages($res);
    }

    /**
     * @param array $completionObject
     * @return string
     */
    protected function collectChatCompletionMessages(array $completionObject): string
    {
        $res = [];
        foreach ($completionObject['choices'] ?? [] as $completion) {
            $res[] = $completion['message']['content'] ?? '';
        }
        return join("\n", $res);
    }

    /**
     * @param string $suffix
     * @return string
     */
    protected function baseUrl(string $suffix): string
    {
        return 'https://api.openai.com/v1/' . $suffix;
    }

    /**
     * @return string[]
     */
    protected function headers(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->api_key,
            'Content-Type' => 'application/json',
        ];
    }
}