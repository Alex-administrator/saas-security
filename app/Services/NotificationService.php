<?php
declare(strict_types=1);

namespace App\Services;

use App\Support\Logger;

final class NotificationService
{
    public function handleArticlePublished(array $payload): void
    {
        Logger::info('Article published notification prepared', [
            'article_id' => $payload['article_id'] ?? null,
            'organization_id' => $payload['organization_id'] ?? null,
            'title' => $payload['title'] ?? null,
            'public_url' => url('blog/' . ($payload['slug'] ?? '')),
        ]);
    }
}

