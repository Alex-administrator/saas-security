<?php
declare(strict_types=1);

return [
    'enabled' => filter_var(env('OPENAI_ENABLED', 'false'), FILTER_VALIDATE_BOOL),
    'api_key' => env('OPENAI_API_KEY', ''),
    'model' => env('OPENAI_MODEL', 'gpt-4.1-mini'),
    'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
];

