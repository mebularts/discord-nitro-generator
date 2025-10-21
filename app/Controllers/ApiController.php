<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Riddle;

require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../db.php';

class ApiController
{
    public function vote(): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $id = (int) ($input['id'] ?? 0);
        $direction = ((int) ($input['dir'] ?? 0)) === 1 ? 1 : -1;

        if ($id <= 0) {
            json_response(['ok' => false, 'error' => 'invalid id'], 422);
        }

        $pdo = db();
        $pdo->beginTransaction();
        $ip = @inet_pton($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
        $ua = hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? '');

        $insert = $pdo->prepare('INSERT IGNORE INTO votes(riddle_id, direction, ip, ua_hash) VALUES(?,?,?,?)');
        $insert->execute([$id, $direction, $ip, $ua]);

        if ($insert->rowCount() > 0) {
            $column = $direction === 1 ? 'up_votes' : 'down_votes';
            $pdo->prepare("UPDATE riddles SET {$column} = {$column} + 1 WHERE id = ?")->execute([$id]);
        }

        $scoreStmt = $pdo->prepare('SELECT up_votes, down_votes FROM riddles WHERE id = ?');
        $scoreStmt->execute([$id]);
        $row = $scoreStmt->fetch();
        if (!$row) {
            $pdo->rollBack();
            json_response(['ok' => false, 'error' => 'not found'], 404);
        }

        $pdo->commit();
        $total = max(1, (int) $row['up_votes'] + (int) $row['down_votes']);
        $percent = round(((int) $row['up_votes'] / $total) * 100, 1);
        json_response(['ok' => true, 'percent' => $percent]);
    }

    public function list(): void
    {
        $filters = [
            'q'          => trim($_GET['q'] ?? ''),
            'difficulty' => $_GET['difficulty'] ?? null,
            'length'     => $_GET['length'] ?? null,
            'category'   => $_GET['category'] ?? null,
            'page'       => max(1, (int) ($_GET['page'] ?? 1)),
            'per_page'   => min(100, max(1, (int) ($_GET['per_page'] ?? 20))),
            'sort'       => $_GET['sort'] ?? 'pop',
        ];

        $data = Riddle::paginate($filters);
        json_response([
            'ok'    => true,
            'data'  => $data['items'],
            'total' => $data['total'],
            'page'  => $data['page'],
            'per'   => $data['per'],
        ]);
    }
}
