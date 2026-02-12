<?php

namespace Vanguard\Modules\AiRequests;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\MimeType;

class GeminiAiApi implements StdAiCalls
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
    public function __construct(string $api_key, string $model = '2.5-flash')
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
        $imageMime = MimeType::fromExtension(pathinfo($imageName, PATHINFO_EXTENSION));
        $settings = [
            'image' => $imageContent,
            'image_mime' => $imageMime,
        ];
        return $this->generateContent($prompt, $settings);
    }

    /**
     * @param string $clientQuestion
     * @param string $systemContext
     * @return string
     */
    public function textGeneration(string $clientQuestion, string $systemContext = ''): string
    {
        $settings = [];
        if ($systemContext) {
            $settings['system'] = $systemContext;
        }
        return $this->generateContent($clientQuestion, $settings);
    }

    /**
     * @param string $clientQuestion
     * @param array $moreSettings
     * @return string
     */
    protected function generateContent(string $clientQuestion, array $moreSettings = []): string
    {
        $curl = new Client();
        try {
            $json = [
                'contents' => [
                    'parts' => [
                        'text' => $clientQuestion,
                    ]
                ]
            ];
            if (!empty($moreSettings['image'])) {
                $json['contents']['parts'] = [
                    [
                        'text' => $clientQuestion,
                    ],
                    [
                        'inline_data' => [
                            'mime_type' => $moreSettings['image_mime'] ?? 'image/jpeg',
                            'data' => base64_encode($moreSettings['image']),
                        ]
                    ]
                ];
            }
            if (!empty($moreSettings['system'])) {
                $json['system_instruction'] = [
                    'parts' => [
                        'text' => $moreSettings['system'],
                    ]
                ];
            }

            $response = $curl->post($this->baseUrl('models/gemini-'.strtolower($this->model).':generateContent'), [
                'headers' => $this->headers(),
                'json' => $json,
            ]);
        } catch (Exception $e) {
            return $e->getMessage();
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
        foreach ($completionObject['candidates'] ?? [] as $completion) {
            foreach ($completion['content']['parts'] ?? [] as $part) {
                $res[] = $part['text'] ?? '';
            }
        }
        return join("\n", $res);
    }

    /**
     * @param string $suffix
     * @return string
     */
    protected function baseUrl(string $suffix): string
    {
        return 'https://generativelanguage.googleapis.com/v1beta/' . $suffix . '?key=' . $this->api_key;
    }

    /**
     * @return string[]
     */
    protected function headers(): array
    {
        return [
            'Content-Type' => 'application/json',
        ];
    }
}