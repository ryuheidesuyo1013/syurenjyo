<?php

declare(strict_types=1);

final class CategoryRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * カテゴリ一覧を取得する
     */
    public function findAll(): array
    {
        $sql = '
            SELECT
                c.id,
                c.name,
                c.slug,
                c.created_at,
                COUNT(a.id) AS article_count
            FROM categories AS c
            LEFT JOIN articles AS a
                ON a.category_id = c.id
            GROUP BY
                c.id,
                c.name,
                c.slug,
                c.created_at
            ORDER BY c.name
        ';

        return $this
            ->pdo
            ->query($sql)
            ->fetchAll();
    }

    /**
     * IDを指定してカテゴリを取得する
     */
    public function findById(int $id): array|false
    {
        $sql = '
            SELECT
                id,
                name,
                slug,
                created_at
            FROM categories
            WHERE id = :id
            LIMIT 1
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':id' => $id,
        ]);

        return $stmt->fetch();
    }

    /**
     * スラッグを指定してカテゴリを取得する
     */
    public function findBySlug(string $slug): array|false
    {
        $sql = '
            SELECT
                id,
                name,
                slug,
                created_at
            FROM categories
            WHERE slug = :slug
            LIMIT 1
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':slug' => $slug,
        ]);

        return $stmt->fetch();
    }

    /**
     * 指定されたIDのカテゴリが存在するか確認する
     */
    public function existsById(int $id): bool
    {
        $sql = '
            SELECT EXISTS (
                SELECT 1
                FROM categories
                WHERE id = :id
            )
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':id' => $id,
        ]);

        return (bool) $stmt->fetchColumn();
    }

    /**
     * カテゴリを作成する
     */
    public function create(
        string $name,
        string $slug
    ): void {
        $sql = '
            INSERT INTO categories (
                name,
                slug
            ) VALUES (
                :name,
                :slug
            )
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':name' => $name,
            ':slug' => $slug,
        ]);
    }

    /**
     * カテゴリを更新する
     */
    public function update(
        int $id,
        string $name,
        string $slug
    ): void {
        $sql = '
            UPDATE categories
            SET
                name = :name,
                slug = :slug
            WHERE id = :id
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':name' => $name,
            ':slug' => $slug,
            ':id' => $id,
        ]);
    }

    /**
     * カテゴリを削除する
     */
    public function delete(int $id): void
    {
        $sql = '
            DELETE FROM categories
            WHERE id = :id
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':id' => $id,
        ]);
    }

    /**
     * カテゴリに所属する記事数を取得する
     */
    public function countArticles(int $id): int
    {
        $sql = '
            SELECT COUNT(*)
            FROM articles
            WHERE category_id = :category_id
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':category_id' => $id,
        ]);

        return (int) $stmt->fetchColumn();
    }
}