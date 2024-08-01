<?php

namespace App\GPT\Actions\TranslateText;

use MalteKuhr\LaravelGPT\GPTAction;
use Closure;

class TranslateTextGPTAction extends GPTAction
{
    public function __construct(
        protected string $text,
    ) {
    }

    /**
     * Provides the system message to guide the GPT model.
     *
     * @return string|null
     */
    public function systemMessage(): ?string
    {
        return 'Translate the following product from English to Arabic.';
    }

    /**
     * Specifies the function to be invoked by the model.
     *
     * @return Closure
     */
    public function function(): Closure
    {
        return function (string $text): string {
            $openai = app('openai');

            $prompt = "Translate the following text to Arabic: \"$text\"";

            $response = $openai->completions()->create([
                'model' => 'text-davinci-003',
                'prompt' => $prompt,
                'max_tokens' => 150,
            ]);

            $translatedText = $response['choices'][0]['text'] ?? '';

            return trim($translatedText);
        };
    }

    /**
     * Defines the rules for input validation and JSON schema generation.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'text' => 'required|string',
        ];
    }
}
