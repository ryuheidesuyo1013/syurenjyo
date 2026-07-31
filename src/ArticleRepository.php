<?php

declare(strict_types=1);

final class ArticleRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * 管理画面用の記事一覧を、指定された条件と範囲で取得する
     */
    public function findAll(
        string $sort = 'created_desc',
        int $limit = 10,
        int $offset = 0,
        string $keyword = '',
        string $status = '',
        int $categoryId = 0
    ): array {
        $orderBy = $this->getAdminOrderBy($sort);

        $limit = max(1, $limit);
        $offset = max(0, $offset);

        $sql = '
            SELECT
                a.id,
                a.title,
                a.category_id,
                c.name AS category,
                c.slug AS category_slug,
                a.status,
                a.published_at,
                a.created_at,
                a.updated_at
            FROM articles AS a
            INNER JOIN categories AS c
                ON c.id = a.category_id
            WHERE 1 = 1
        ';

        $params = [];

        if ($keyword !== '') {
            $sql .= '
                AND (
                    a.title LIKE :keyword_title
                    OR a.summary LIKE :keyword_summary
                    OR a.content LIKE :keyword_content
                )
            ';

            $searchKeyword = '%' . $keyword . '%';

            $params[':keyword_title'] = $searchKeyword;
            $params[':keyword_summary'] = $searchKeyword;
            $params[':keyword_content'] = $searchKeyword;
        }

        if (in_array($status, ['published', 'draft'], true)) {
            $sql .= '
                AND a.status = :status
            ';

            $params[':status'] = $status;
        }

        if ($categoryId > 0) {
            $sql .= '
                AND a.category_id = :category_id
            ';

            $params[':category_id'] = $categoryId;
        }

        $sql .= '
            ORDER BY ' . $orderBy . '
            LIMIT :limit
            OFFSET :offset
        ';

        $stmt = $this->pdo->prepare($sql);

        foreach ($params as $name => $value) {
            $parameterType = $name === ':category_id'
                ? PDO::PARAM_INT
                : PDO::PARAM_STR;

            $stmt->bindValue(
                $name,
                $value,
                $parameterType
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

    /**
     * 管理画面の記事一覧で使用する並び順を返す
     */
    private function getAdminOrderBy(string $sort): string
    {
        $sortOptions = [
            'created_desc' => 'a.created_at DESC, a.id DESC',
            'created_asc' => 'a.created_at ASC, a.id ASC',
            'updated_desc' => 'a.updated_at DESC, a.id DESC',
            'updated_asc' => 'a.updated_at ASC, a.id ASC',
            'published_desc' => '
                a.published_at IS NULL ASC,
                a.published_at DESC,
                a.id DESC
            ',
            'published_asc' => '
                a.published_at IS NULL ASC,
                a.published_at ASC,
                a.id ASC
            ',
            'title_asc' => 'a.title ASC, a.id ASC',
            'title_desc' => 'a.title DESC, a.id DESC',
            'category_asc' => '
                c.name ASC,
                a.title ASC,
                a.id ASC
            ',
            'status_asc' => '
                CASE
                    WHEN a.status = \'published\' THEN 1
                    WHEN a.status = \'draft\' THEN 2
                    ELSE 3
                END ASC,
                a.updated_at DESC,
                a.id DESC
            ',
            'status_desc' => '
                CASE
                    WHEN a.status = \'draft\' THEN 1
                    WHEN a.status = \'published\' THEN 2
                    ELSE 3
                END ASC,
                a.updated_at DESC,
                a.id DESC
            ',
        ];

        return $sortOptions[$sort]
            ?? $sortOptions['created_desc'];
    }

    /**
     * IDを指定して記事を取得する
     */
    public function findById(int $id): array|false
    {
        $sql = '
            SELECT
                a.id,
                a.title,
                a.slug,
                a.category_id,
                c.name AS category,
                c.slug AS category_slug,
                a.summary,
                a.content,
                a.status,
                a.published_at
            FROM articles AS a
            INNER JOIN categories AS c
                ON c.id = a.category_id
            WHERE a.id = :id
            LIMIT 1
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':id' => $id,
        ]);

        return $stmt->fetch();
    }

    /**
     * 公開中の記事一覧を取得する
     */
    public function findPublished(): array
    {
        $sql = '
            SELECT
                a.id,
                a.title,
                a.slug,
                a.category_id,
                c.name AS category,
                c.slug AS category_slug,
                a.summary,
                a.published_at
            FROM articles AS a
            INNER JOIN categories AS c
                ON c.id = a.category_id
            WHERE a.status = :status
              AND a.published_at IS NOT NULL
              AND a.published_at <= NOW()
            ORDER BY a.published_at DESC
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':status' => 'published',
        ]);

        return $stmt->fetchAll();
    }

    /**
     * スラッグを指定して公開記事を取得する
     */
    public function findPublishedBySlug(string $slug): array|false
    {
        $sql = '
            SELECT
                a.id,
                a.title,
                a.slug,
                a.category_id,
                c.name AS category,
                c.slug AS category_slug,
                a.summary,
                a.content,
                a.published_at,
                a.updated_at
            FROM articles AS a
            INNER JOIN categories AS c
                ON c.id = a.category_id
            WHERE a.slug = :slug
              AND a.status = :status
              AND a.published_at IS NOT NULL
              AND a.published_at <= NOW()
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
                a.id,
                a.title,
                a.slug,
                a.category_id,
                c.name AS category,
                c.slug AS category_slug,
                a.summary,
                a.published_at
            FROM articles AS a
            INNER JOIN categories AS c
                ON c.id = a.category_id
            WHERE a.status = :status
              AND a.published_at IS NOT NULL
              AND a.published_at <= NOW()
        ';

        $params = [
            ':status' => 'published',
        ];

        if ($keyword !== '') {
            $sql .= '
                AND (
                    a.title LIKE :keyword_title
                    OR a.summary LIKE :keyword_summary
                    OR a.content LIKE :keyword_content
                )
            ';

            $searchKeyword = '%' . $keyword . '%';

            $params[':keyword_title'] = $searchKeyword;
            $params[':keyword_summary'] = $searchKeyword;
            $params[':keyword_content'] = $searchKeyword;
        }

        if ($category !== '') {
            $sql .= '
                AND c.name = :category
            ';

            $params[':category'] = $category;
        }

        $sql .= '
            ORDER BY a.published_at DESC
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
            FROM articles AS a
            INNER JOIN categories AS c
                ON c.id = a.category_id
            WHERE a.status = :status
              AND a.published_at IS NOT NULL
              AND a.published_at <= NOW()
        ';

        $params = [
            ':status' => 'published',
        ];

        if ($keyword !== '') {
            $sql .= '
                AND (
                    a.title LIKE :keyword_title
                    OR a.summary LIKE :keyword_summary
                    OR a.content LIKE :keyword_content
                )
            ';

            $searchKeyword = '%' . $keyword . '%';

            $params[':keyword_title'] = $searchKeyword;
            $params[':keyword_summary'] = $searchKeyword;
            $params[':keyword_content'] = $searchKeyword;
        }

        if ($category !== '') {
            $sql .= '
                AND c.name = :category
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
                a.id,
                a.title,
                a.slug,
                a.category_id,
                c.name AS category,
                c.slug AS category_slug,
                a.summary,
                a.published_at
            FROM articles AS a
            INNER JOIN categories AS c
                ON c.id = a.category_id
            WHERE a.status = :status
              AND a.published_at IS NOT NULL
              AND a.published_at <= NOW()
        ';

        $params = [
            ':status' => 'published',
        ];

        if ($keyword !== '') {
            $sql .= '
                AND (
                    a.title LIKE :keyword_title
                    OR a.summary LIKE :keyword_summary
                    OR a.content LIKE :keyword_content
                )
            ';

            $searchKeyword = '%' . $keyword . '%';

            $params[':keyword_title'] = $searchKeyword;
            $params[':keyword_summary'] = $searchKeyword;
            $params[':keyword_content'] = $searchKeyword;
        }

        if ($category !== '') {
            $sql .= '
                AND c.name = :category
            ';

            $params[':category'] = $category;
        }

        $sql .= '
            ORDER BY a.published_at DESC
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

    /**
     * 管理画面の検索条件に一致する記事数を取得する
     */
    public function countAdminSearch(
        string $keyword = '',
        string $status = '',
        int $categoryId = 0
    ): int {
        $sql = '
            SELECT COUNT(*)
            FROM articles AS a
            WHERE 1 = 1
        ';

        $params = [];

        if ($keyword !== '') {
            $sql .= '
                AND (
                    a.title LIKE :keyword_title
                    OR a.summary LIKE :keyword_summary
                    OR a.content LIKE :keyword_content
                )
            ';

            $searchKeyword = '%' . $keyword . '%';

            $params[':keyword_title'] = $searchKeyword;
            $params[':keyword_summary'] = $searchKeyword;
            $params[':keyword_content'] = $searchKeyword;
        }

        if (in_array($status, ['published', 'draft'], true)) {
            $sql .= '
                AND a.status = :status
            ';

            $params[':status'] = $status;
        }

        if ($categoryId > 0) {
            $sql .= '
                AND a.category_id = :category_id
            ';

            $params[':category_id'] = $categoryId;
        }

        $stmt = $this->pdo->prepare($sql);

        foreach ($params as $name => $value) {
            $parameterType = $name === ':category_id'
                ? PDO::PARAM_INT
                : PDO::PARAM_STR;

            $stmt->bindValue(
                $name,
                $value,
                $parameterType
            );
        }

        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    /**
     * 登録されているすべての記事数を取得する
     */
    public function countAll(): int
    {
        $sql = '
            SELECT COUNT(*)
            FROM articles
        ';

        return (int) $this
            ->pdo
            ->query($sql)
            ->fetchColumn();
    }

    /**
     * 現在公開されている記事数を取得する
     */
    public function countPublished(): int
    {
        $sql = '
            SELECT COUNT(*)
            FROM articles
            WHERE status = :status
              AND published_at IS NOT NULL
              AND published_at <= NOW()
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':status' => 'published',
        ]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * 下書きの記事数を取得する
     */
    public function countDraft(): int
    {
        $sql = '
            SELECT COUNT(*)
            FROM articles
            WHERE status = :status
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':status' => 'draft',
        ]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * 管理画面のダッシュボードに表示する最新記事を取得する
     */
    public function findLatest(int $limit = 5): array
    {
        $limit = max(1, $limit);

        $sql = '
            SELECT
                a.id,
                a.title,
                a.category_id,
                c.name AS category,
                c.slug AS category_slug,
                a.status,
                a.published_at,
                a.created_at,
                a.updated_at
            FROM articles AS a
            INNER JOIN categories AS c
                ON c.id = a.category_id
            ORDER BY a.created_at DESC
            LIMIT :limit
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(
            ':limit',
            $limit,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * 記事を作成する
     */
    public function create(
        array $article,
        ?string $publishedAt
    ): void {
        $sql = '
            INSERT INTO articles (
                title,
                slug,
                category_id,
                summary,
                content,
                status,
                published_at
            ) VALUES (
                :title,
                :slug,
                :category_id,
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
            ':category_id' => $article['category_id'],
            ':summary' => $article['summary'],
            ':content' => $article['content'],
            ':status' => $article['status'],
            ':published_at' => $publishedAt,
        ]);
    }

    /**
     * 記事を更新する
     */
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
                category_id = :category_id,
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
            ':category_id' => $article['category_id'],
            ':summary' => $article['summary'],
            ':content' => $article['content'],
            ':status' => $article['status'],
            ':published_at' => $publishedAt,
            ':id' => $id,
        ]);
    }

    /**
     * 記事を削除する
     */
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