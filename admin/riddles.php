<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_admin();

$pdo = db();
$categories = $pdo->query('SELECT id, name, slug FROM categories ORDER BY name ASC')->fetchAll() ?: [];

$editing = null;
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare('SELECT * FROM riddles WHERE id = :id');
    $stmt->execute(['id' => (int) $_GET['id']]);
    $editing = $stmt->fetch() ?: null;
}

if (isset($_POST['delete'])) {
    verify_csrf_token();
    $id = (int) $_POST['delete'];
    $pdo->prepare('DELETE FROM riddles WHERE id = :id')->execute(['id' => $id]);
    $pdo->prepare('DELETE FROM riddle_category WHERE riddle_id = :id')->execute(['id' => $id]);
    flash('success', 'Riddle deleted');
    redirect('/admin/riddles.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $id   = isset($_POST['id']) ? (int) $_POST['id'] : null;
    $data = [
        'title'        => trim($_POST['title'] ?? ''),
        'slug'         => trim($_POST['slug'] ?? ''),
        'body'         => trim($_POST['body'] ?? ''),
        'answer'       => trim($_POST['answer'] ?? ''),
        'difficulty'   => $_POST['difficulty'] ?? 'medium',
        'locale'       => $_POST['locale'] ?? APP_LOCALE,
        'is_published' => isset($_POST['is_published']) ? 1 : 0,
    ];
    $categoryIds = array_map('intval', $_POST['categories'] ?? []);

    if ($data['slug'] === '') {
        $data['slug'] = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $data['title']));
    }

    if ($id) {
        $stmt = $pdo->prepare('UPDATE riddles SET title = :title, slug = :slug, body = :body, answer = :answer, difficulty = :difficulty, locale = :locale, is_published = :is_published, updated_at = NOW() WHERE id = :id');
        $stmt->execute($data + ['id' => $id]);
        $pdo->prepare('DELETE FROM riddle_category WHERE riddle_id = :id')->execute(['id' => $id]);
        $riddleId = $id;
    } else {
        $stmt = $pdo->prepare('INSERT INTO riddles (title, slug, body, answer, difficulty, locale, is_published, published_at, created_at) VALUES (:title, :slug, :body, :answer, :difficulty, :locale, :is_published, NOW(), NOW())');
        $stmt->execute($data);
        $riddleId = (int) $pdo->lastInsertId();
    }

    $pivot = $pdo->prepare('INSERT INTO riddle_category (riddle_id, category_id) VALUES (:riddle_id, :category_id)');
    foreach ($categoryIds as $categoryId) {
        $pivot->execute(['riddle_id' => $riddleId, 'category_id' => $categoryId]);
    }

    flash('success', 'Riddle saved');
    redirect('/admin/riddles.php');
}

$riddles = $pdo->query('SELECT r.*, (SELECT COUNT(*) FROM votes WHERE riddle_id = r.id) as votes FROM riddles r ORDER BY published_at DESC')->fetchAll() ?: [];

$selectedCategories = [];
if ($editing) {
    $stmt = $pdo->prepare('SELECT category_id FROM riddle_category WHERE riddle_id = :id');
    $stmt->execute(['id' => $editing['id']]);
    $selectedCategories = array_column($stmt->fetchAll() ?: [], 'category_id');
}

echo admin_view('riddles', [
    'title' => 'Riddles',
    'active' => 'riddles',
    'riddles' => $riddles,
    'categories' => $categories,
    'editing' => $editing,
    'selectedCategories' => $selectedCategories,
    'flash' => flash('success'),
]);
