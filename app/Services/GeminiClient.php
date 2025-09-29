<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiClient
{
    public function __construct(
        private ?string $apiKey = null,
        private ?string $model = null,
        private ?string $base  = null,
    ) {
        $this->apiKey = $this->apiKey ?: env('GEMINI_API_KEY');
        $this->model  = $this->model  ?: env('GEMINI_MODEL', 'gemini-1.5-flash');
        $this->base   = rtrim($this->base ?: env('GEMINI_API_BASE', 'https://generativelanguage.googleapis.com/v1beta'), '/');
    }

    /**
     * @param array $history  // [{role:'user|model', parts:[{text:'...'}]}...]
     * @param array $config   // generationConfig override
     * @return array ['text' => string, 'raw' => array]
     */
    public function generate(array $history, array $config = []): array
    {
        $url = "{$this->base}/models/{$this->model}:generateContent?key={$this->apiKey}";
        $payload = [
            'contents' => $history,
            'generationConfig' => $config + [
                'temperature'      => 0.7,
                'maxOutputTokens'  => 1024,
                'topP'             => 0.95,
                'topK'             => 40,
                'thinkingConfig'   => ['thinkingBudget' => 128],
                'responseMimeType' => 'text/plain',

            ],
        ];

        $resp = Http::timeout(60)->post($url, $payload);
        if (!$resp->ok()) {
            throw new \RuntimeException("Gemini error: {$resp->status()} {$resp->body()}");
        }
        $data = $resp->json();

        $finish = data_get($data, 'candidates.0.finishReason');
        $text   = data_get($data, 'candidates.0.content.parts.0.text', '');

        if ($text === '' || $finish === 'SAFETY') {
            Log::warning('Gemini returned empty text or SAFETY', [
                'finishReason'   => $finish,
                'promptFeedback' => data_get($data, 'promptFeedback'),
                'safetyRatings'  => data_get($data, 'candidates.0.safetyRatings'),
                'usageMetadata'  => data_get($data, 'usageMetadata'),
                'modelVersion'   => data_get($data, 'modelVersion'),
            ]);
        }

        return ['text' => $text, 'raw' => $data];
    }
}
