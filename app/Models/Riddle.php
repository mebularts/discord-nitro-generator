<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

use function cache_remember;
use function db;

class Riddle
{
    public static function paginate(array $options): array
    {
        $pdo = db();
        $where = ['r.status = "published"'];
        $params = [];

        if (!empty($options['difficulty'])) {
            $where[] = 'r.difficulty = ?';
            $params[] = $options['difficulty'];
        }
        if (!empty($options['length'])) {
            $where[] = 'r.length = ?';
            $params[] = $options['length'];
        }
        if (!empty($options['category'])) {
            $where[] = 'EXISTS(SELECT 1 FROM riddle_category rc JOIN categories c ON c.id = rc.category_id WHERE rc.riddle_id = r.id AND c.slug = ?)';
            $params[] = $options['category'];
        }
        if (!empty($options['q'])) {
            $like = '%' . $options['q'] . '%';
            $where[] = '(r.title LIKE ? OR r.body LIKE ?)';
            $params[] = $like;
            $params[] = $like;
        }

        $whereSql = 'WHERE ' . implode(' AND ', $where);
        $sortKey = $options['sort'] ?? 'pop';
        $orderBy = $sortKey === 'new'
            ? 'r.published_at DESC, r.id DESC'
            : '(r.up_votes - r.down_votes) DESC, r.views DESC, r.id DESC';

        $page = max(1, (int) ($options['page'] ?? 1));
        $perPage = min(60, max(10, (int) ($options['per_page'] ?? 20)));
        $offset = ($page - 1) * $perPage;

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM riddles r {$whereSql}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $listStmt = $pdo->prepare("SELECT r.* FROM riddles r {$whereSql} ORDER BY {$orderBy} LIMIT {$perPage} OFFSET {$offset}");
        $listStmt->execute($params);
        $items = array_map([self::class, 'decorate'], $listStmt->fetchAll(PDO::FETCH_ASSOC) ?: []);

        return [
            'items' => $items,
            'total' => $total,
            'page'  => $page,
            'per'   => $perPage,
        ];
    }

    public static function topVoted(int $limit = 6): array
    {
        $stmt = db()->prepare('SELECT * FROM riddles r WHERE r.status = "published" ORDER BY (r.up_votes - r.down_votes) DESC, r.views DESC LIMIT ?');
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return array_map([self::class, 'decorate'], $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
    }

    public static function recent(int $limit = 6): array
    {
        $stmt = db()->prepare('SELECT * FROM riddles r WHERE r.status = "published" ORDER BY r.published_at DESC, r.id DESC LIMIT ?');
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return array_map([self::class, 'decorate'], $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
    }

    public static function stats(): array
    {
        return cache_remember('riddle:stats', 300, static function () {
            $pdo = db();
            $riddleCount = (int) $pdo->query('SELECT COUNT(*) FROM riddles WHERE status = "published"')->fetchColumn();
            $userCount = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
            $voteCount = (int) $pdo->query('SELECT SUM(up_votes + down_votes) FROM riddles')->fetchColumn();
            $views = (int) $pdo->query('SELECT SUM(views) FROM riddles')->fetchColumn();
            return [
                'riddles' => $riddleCount,
                'users'   => $userCount,
                'votes'   => $voteCount,
                'views'   => $views,
            ];
        });
    }

    public static function findBySlug(string $slug): ?array
    {
        $stmt = db()->prepare('SELECT * FROM riddles WHERE slug = ? AND status = "published" LIMIT 1');
        $stmt->execute([$slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        return self::decorate($row);
    }

    public static function incrementViews(int $id): void
    {
        $stmt = db()->prepare('UPDATE riddles SET views = views + 1 WHERE id = ?');
        $stmt->execute([$id]);
    }

    public static function related(int $id, string $difficulty, int $limit = 4): array
    {
        $stmt = db()->prepare('SELECT * FROM riddles WHERE status = "published" AND id <> ? AND difficulty = ? ORDER BY (up_votes - down_votes) DESC, views DESC LIMIT ?');
        $stmt->bindValue(1, $id, PDO::PARAM_INT);
        $stmt->bindValue(2, $difficulty);
        $stmt->bindValue(3, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return array_map([self::class, 'decorate'], $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
    }

    public static function sitemapEntries(): array
    {
        $stmt = db()->query('SELECT slug, updated_at, published_at FROM riddles WHERE status = "published" ORDER BY (updated_at IS NULL), updated_at DESC, published_at DESC');
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    private static function decorate(array $riddle): array
    {
        $riddle['categories'] = self::categoriesFor((int) $riddle['id']);
        $riddle['tags'] = self::tagsFor((int) $riddle['id']);
        $totalVotes = max(1, (int) $riddle['up_votes'] + (int) $riddle['down_votes']);
        $riddle['approval'] = round(((int) $riddle['up_votes'] / $totalVotes) * 100, 1);
        return $riddle;
    }

    private static function categoriesFor(int $riddleId): array
    {
        $stmt = db()->prepare('SELECT c.slug, c.name FROM categories c JOIN riddle_category rc ON rc.category_id = c.id WHERE rc.riddle_id = ? ORDER BY c.sort_order ASC, c.name ASC');
        $stmt->execute([$riddleId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private static function tagsFor(int $riddleId): array
    {
        $stmt = db()->prepare('SELECT t.slug, t.name FROM tags t JOIN riddle_tag rt ON rt.tag_id = t.id WHERE rt.riddle_id = ? ORDER BY t.name ASC');
        $stmt->execute([$riddleId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
