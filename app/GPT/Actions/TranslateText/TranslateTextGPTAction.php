<?php

namespace App\GPT\Actions\TranslateText;

use MalteKuhr\LaravelGPT\GPTAction;
use Closure;

class TranslateTextGPTAction extends GPTAction
{

    public function __construct(
        protected $product
    ) {
    }

    /**
     * Provides the system message to guide the GPT model.
     *
     * @return string|null
     */
    public function systemMessage(): ?string
    {
        return 'Translate the product title and description from English to Arabic.';
    }

    /**
     * Specifies the function to be invoked by the model.
     *
     * @return Closure
     */
    public function function(): Closure
    {
        return function (): mixed {
            return [
                'product' => $this->product
            ];
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
            'product' => 'required|array',
        ];
    }
}
