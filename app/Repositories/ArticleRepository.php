<?php
declare(strict_types=1);

namespace App\Repositories;

final class ArticleRepository extends BaseRepository
{
    public function listPublic(int $limit = 12): array
    {
        return $this->fetchAll(
            'SELECT a.*, o.name AS organization_name
             FROM articles a
             INNER JOIN organizations o ON o.id = a.organization_id
             WHERE a.status = :status
             ORDER BY a.published_at DESC
             LIMIT ' . (int) $limit,
            ['status' => 'published']
        );
    }

    public function listByOrganization(int $organizationId): array
    {
        return $this->fetchAll(
            'SELECT a.*, u.name AS author_name
             FROM articles a
             INNER JOIN users u ON u.id = a.author_id
             WHERE a.organization_id = :organization_id
             ORDER BY a.created_at DESC',
            ['organization_id' => $organizationId]
        );
    }

    public function recentByOrganization(int $organizationId, int $limit = 5): array
    {
        return $this->fetchAll(
            'SELECT a.*, u.name AS author_name
             FROM articles a
             INNER JOIN users u ON u.id = a.author_id
             WHERE a.organization_id = :organization_id
             ORDER BY a.created_at DESC
             LIMIT ' . (int) $limit,
            ['organization_id' => $organizationId]
        );
    }

    public function create(array $data): int
    {
        $this->execute(
            'INSERT INTO articles (organization_id, author_id, title, slug, excerpt, content, status, cover_image, created_at, updated_at)
             VALUES (:organization_id, :author_id, :title, :slug, :excerpt, :content, :status, :cover_image, UTC_TIMESTAMP(), UTC_TIMESTAMP())',
            [
                'organization_id' => $data['organization_id'],
                'author_id' => $data['author_id'],
                'title' => $data['title'],
                'slug' => $data['slug'],
                'excerpt' => $data['excerpt'],
                'content' => $data['content'],
                'status' => $data['status'],
                'cover_image' => $data['cover_image'] ?? null,
            ]
        );

        return (int) $this->pdo->lastInsertId();
    }

    public function findByIdAndOrganization(int $id, int $organizationId): ?array
    {
        return $this->fetchOne(
            'SELECT * FROM articles WHERE id = :id AND organization_id = :organization_id LIMIT 1',
            ['id' => $id, 'organization_id' => $organizationId]
        );
    }

    public function findPublicBySlug(string $slug): ?array
    {
        return $this->fetchOne(
            'SELECT a.*, o.name AS organization_name
             FROM articles a
             INNER JOIN organizations o ON o.id = a.organization_id
             WHERE a.slug = :slug AND a.status = :status
             LIMIT 1',
            ['slug' => $slug, 'status' => 'published']
        );
    }

    public function slugExists(string $slug): bool
    {
        $row = $this->fetchOne('SELECT id FROM articles WHERE slug = :slug LIMIT 1', ['slug' => $slug]);
        return $row !== null;
    }

    public function publish(int $id, int $organizationId): void
    {
        $this->execute(
            'UPDATE articles SET status = :status, published_at = UTC_TIMESTAMP(), updated_at = UTC_TIMESTAMP()
             WHERE id = :id AND organization_id = :organization_id',
            ['status' => 'published', 'id' => $id, 'organization_id' => $organizationId]
        );
    }

    public function searchPublic(string $term): array
    {
        $term = '%' . $term . '%';
        return $this->fetchAll(
            'SELECT id, title, slug, excerpt, published_at
             FROM articles
             WHERE status = :status AND (title LIKE :term OR excerpt LIKE :term OR content LIKE :term)
             ORDER BY published_at DESC
             LIMIT 20',
            ['status' => 'published', 'term' => $term]
        );
    }
}
