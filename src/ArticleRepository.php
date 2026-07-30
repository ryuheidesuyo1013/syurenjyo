<?php

declare(strict_types=1);

final class ArticleRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    public function findAll(): array
    {
        $sql = '
            SELECT
                id,
                title,
                category,
                status,
                published_at,
                updated_at
            FROM articles
            ORDER BY created_at DESC
        ';

        return $this
            ->pdo
            ->query($sql)
            ->fetchAll();
    }

    public function findById(int $id): array|false
    {
        $sql = '
            SELECT
                id,
                title,
                slug,
                category,
                summary,
                content,
                status,
                published_at
            FROM articles
            WHERE id = :id
            LIMIT 1
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':id' => $id,
        ]);

        return $stmt->fetch();
    }

    public function findPublished(): array
    {
        $sql = '
            SELECT
                id,
                title,
                slug,
                category,
                summary,
                published_at
            FROM articles
            WHERE status = :status
              AND published_at IS NOT NULL
              AND published_at <= NOW()
            ORDER BY published_at DESC
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':status' => 'published',
        ]);

        return $stmt->fetchAll();
    }

    public function findPublishedBySlug(string $slug): array|false
    {
        $sql = '
            SELECT
                id,
                title,
                slug,
                category,
                summary,
                content,
                published_at,
                updated_at
            FROM articles
            WHERE slug = :slug
              AND status = :status
              AND published_at IS NOT NULL
              AND published_at <= NOW()
            LIMIT 1
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':slug' => $slug,
            ':status' => 'published',
        ]);

        return $stmt->fetch();
    }

    public function create(
        array $article,
        ?string $publishedAt
    ): void {
        $sql = '
            INSERT INTO articles (
                title,
                slug,
                category,
                summary,
                content,
                status,
                published_at
            ) VALUES (
                :title,
                :slug,
                :category,
                :summary,
                :content,
                :status,
                :published_at
            )
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':title' => $article['title'],
            ':slug' => $article['slug'],
            ':category' => $article['category'],
            ':summary' => $article['summary'],
            ':content' => $article['content'],
            ':status' => $article['status'],
            ':published_at' => $publishedAt,
        ]);
    }

    public function update(
        int $id,
        array $article,
        ?string $publishedAt
    ): void {
        $sql = '
            UPDATE articles
            SET
                title = :title,
                slug = :slug,
                category = :category,
                summary = :summary,
                content = :content,
                status = :status,
                published_at = :published_at
            WHERE id = :id
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':title' => $article['title'],
            ':slug' => $article['slug'],
            ':category' => $article['category'],
            ':summary' => $article['summary'],
            ':content' => $article['content'],
            ':status' => $article['status'],
            ':published_at' => $publishedAt,
            ':id' => $id,
        ]);
    }

    public function delete(int $id): void
    {
        $sql = '
            DELETE FROM articles
            WHERE id = :id
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':id' => $id,
        ]);
    }
}