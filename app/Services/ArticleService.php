<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\ArticleRepository;
use App\Repositories\AuditLogRepository;
use App\Repositories\JobRepository;
use RuntimeException;

final class ArticleService
{
    public function create(int $organizationId, int $userId, array $data): int
    {
        $repository = new ArticleRepository();
        $slugService = new SlugService();
        $baseSlug = $slugService->make((string) $data['title']);
        $slug = $baseSlug;
        $suffix = 1;

        while ($repository->slugExists($slug)) {
            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }

        $articleId = $repository->create([
            'organization_id' => $organizationId,
            'author_id' => $userId,
            'title' => trim((string) $data['title']),
            'slug' => $slug,
            'excerpt' => trim((string) $data['excerpt']),
            'content' => trim((string) $data['content']),
            'status' => 'draft',
            'cover_image' => trim((string) ($data['cover_image'] ?? '')),
        ]);

        (new AuditLogRepository())->log([
            'organization_id' => $organizationId,
            'user_id' => $userId,
            'action' => 'article.created',
            'entity_type' => 'article',
            'entity_id' => $articleId,
            'context' => ['title' => $data['title']],
        ]);

        return $articleId;
    }

    public function publish(int $articleId, int $organizationId, int $userId): void
    {
        $repository = new ArticleRepository();
        $article = $repository->findByIdAndOrganization($articleId, $organizationId);

        if ($article === null) {
            throw new RuntimeException('Статья не найдена.');
        }

        $repository->publish($articleId, $organizationId);

        (new JobRepository())->enqueue('article_published_notification', [
            'article_id' => $articleId,
            'organization_id' => $organizationId,
            'title' => $article['title'],
            'slug' => $article['slug'],
        ]);

        (new AuditLogRepository())->log([
            'organization_id' => $organizationId,
            'user_id' => $userId,
            'action' => 'article.published',
            'entity_type' => 'article',
            'entity_id' => $articleId,
            'context' => ['title' => $article['title']],
        ]);
    }
}
