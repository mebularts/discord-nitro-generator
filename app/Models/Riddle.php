<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class Riddle
{
    public static function paginated(array $filters = [], int $limit = 12, int $page = 1): array
    {
        $where = ['is_published = 1'];
        $params = [];

        if (!empty($filters['category'])) {
            $where[] = 'riddles.id IN (SELECT riddle_id FROM riddle_category rc INNER JOIN categories c ON c.id = rc.category_id WHERE c.slug = :category)';
            $params['category'] = $filters['category'];
        }

        if (!empty($filters['difficulty'])) {
            $where[] = 'difficulty = :difficulty';
            $params['difficulty'] = $filters['difficulty'];
        }

        if (!empty($filters['length'])) {
            if ($filters['length'] === 'short') {
                $where[] = 'CHAR_LENGTH(body) < 120';
            } elseif ($filters['length'] === 'medium') {
                $where[] = 'CHAR_LENGTH(body) BETWEEN 120 AND 300';
            } elseif ($filters['length'] === 'long') {
                $where[] = 'CHAR_LENGTH(body) > 300';
            }
        }

        $page  = max(1, $page);
        $limit = max(1, $limit);
        $offset = ($page - 1) * $limit;

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $total = db()->prepare('SELECT COUNT(*) FROM riddles ' . $whereSql);
        $total->execute($params);
        $count = (int) $total->fetchColumn();

        $stmt = db()->prepare('SELECT * FROM riddles ' . $whereSql . ' ORDER BY published_at DESC LIMIT :limit OFFSET :offset');
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return [
            'items'      => $items,
            'total'      => $count,
            'current'    => $page,
            'per_page'   => $limit,
            'totalPages' => (int) ceil($count / $limit),
        ];
    }

    public static function findBySlug(string $slug): ?array
    {
        $stmt = db()->prepare('SELECT * FROM riddles WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function findById(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM riddles WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function related(int $id, int $limit = 3): array
    {
        $stmt = db()->prepare('SELECT r.* FROM riddles r
            INNER JOIN riddle_category rc ON rc.riddle_id = r.id
            WHERE rc.category_id IN (SELECT category_id FROM riddle_category WHERE riddle_id = :id)
              AND r.id <> :id AND r.is_published = 1
            ORDER BY r.published_at DESC
            LIMIT :limit');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function recordVote(int $riddleId, int $score, string $hash): bool
    {
        $stmt = db()->prepare('INSERT INTO votes (riddle_id, voter_hash, score, created_at)
            VALUES (:riddle_id, :hash, :score, NOW())
            ON DUPLICATE KEY UPDATE score = :score, updated_at = NOW()');
        return $stmt->execute([
            'riddle_id' => $riddleId,
            'hash'      => $hash,
            'score'     => $score,
        ]);
    }

    public static function voteStats(int $riddleId): array
    {
        $stmt = db()->prepare('SELECT
            SUM(CASE WHEN score = 1 THEN 1 ELSE 0 END) AS upvotes,
            SUM(CASE WHEN score = -1 THEN 1 ELSE 0 END) AS downvotes
        FROM votes WHERE riddle_id = :id');
        $stmt->execute(['id' => $riddleId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['upvotes' => 0, 'downvotes' => 0];
        $up = (int) ($row['upvotes'] ?? 0);
        $down = (int) ($row['downvotes'] ?? 0);
        $total = max(1, $up + $down);
        $percent = (int) round(($up / $total) * 100);
        return ['upvotes' => $up, 'downvotes' => $down, 'percent' => $percent];
    }
}
