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

    /**
     * 公開記事を検索する
     */
    public function searchPublished(
        string $keyword = '',
        string $category = ''
    ): array {
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
        ';

        $params = [
            ':status' => 'published',
        ];

        if ($keyword !== '') {
            $sql .= '
                AND (
                    title LIKE :keyword_title
                    OR summary LIKE :keyword_summary
                    OR content LIKE :keyword_content
                )
            ';

            $searchKeyword = '%' . $keyword . '%';

            $params[':keyword_title'] = $searchKeyword;
            $params[':keyword_summary'] = $searchKeyword;
            $params[':keyword_content'] = $searchKeyword;
        }

        if ($category !== '') {
            $sql .= '
                AND category = :category
            ';

            $params[':category'] = $category;
        }

        $sql .= '
            ORDER BY published_at DESC
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * 検索条件に一致する公開記事の総件数を取得する
     */
    public function countPublishedSearch(
        string $keyword = '',
        string $category = ''
    ): int {
        $sql = '
            SELECT COUNT(*)
            FROM articles
            WHERE status = :status
              AND published_at IS NOT NULL
              AND published_at <= NOW()
        ';

        $params = [
            ':status' => 'published',
        ];

        if ($keyword !== '') {
            $sql .= '
                AND (
                    title LIKE :keyword_title
                    OR summary LIKE :keyword_summary
                    OR content LIKE :keyword_content
                )
            ';

            $searchKeyword = '%' . $keyword . '%';

            $params[':keyword_title'] = $searchKeyword;
            $params[':keyword_summary'] = $searchKeyword;
            $params[':keyword_content'] = $searchKeyword;
        }

        if ($category !== '') {
            $sql .= '
                AND category = :category
            ';

            $params[':category'] = $category;
        }

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    /**
     * 公開記事を検索し、指定された範囲だけ取得する
     */
    public function searchPublishedWithPagination(
        string $keyword = '',
        string $category = '',
        int $limit = 5,
        int $offset = 0
    ): array {
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
        ';

        $params = [
            ':status' => 'published',
        ];

        if ($keyword !== '') {
            $sql .= '
                AND (
                    title LIKE :keyword_title
                    OR summary LIKE :keyword_summary
                    OR content LIKE :keyword_content
                )
            ';

            $searchKeyword = '%' . $keyword . '%';

            $params[':keyword_title'] = $searchKeyword;
            $params[':keyword_summary'] = $searchKeyword;
            $params[':keyword_content'] = $searchKeyword;
        }

        if ($category !== '') {
            $sql .= '
                AND category = :category
            ';

            $params[':category'] = $category;
        }

        $sql .= '
            ORDER BY published_at DESC
            LIMIT :limit
            OFFSET :offset
        ';

        $stmt = $this->pdo->prepare($sql);

        foreach ($params as $name => $value) {
            $stmt->bindValue(
                $name,
                $value,
                PDO::PARAM_STR
            );
        }

        $stmt->bindValue(
            ':limit',
            $limit,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':offset',
            $offset,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return $stmt->fetchAll();
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