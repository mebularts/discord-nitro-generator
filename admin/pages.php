<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_admin();

$pdo = db();
$editing = null;
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare('SELECT * FROM pages WHERE id = :id');
    $stmt->execute(['id' => (int) $_GET['id']]);
    $editing = $stmt->fetch() ?: null;
}

if (isset($_POST['delete'])) {
    verify_csrf_token();
    $id = (int) $_POST['delete'];
    $pdo->prepare('DELETE FROM pages WHERE id = :id')->execute(['id' => $id]);
    flash('success', 'Page deleted');
    redirect('/admin/pages.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $id = isset($_POST['id']) ? (int) $_POST['id'] : null;
    $data = [
        'title'   => trim($_POST['title'] ?? ''),
        'slug'    => trim($_POST['slug'] ?? ''),
        'body'    => $_POST['body'] ?? '',
        'locale'  => $_POST['locale'] ?? APP_LOCALE,
    ];

    if ($data['slug'] === '') {
        $data['slug'] = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $data['title']));
    }

    if ($id) {
        $stmt = $pdo->prepare('UPDATE pages SET title = :title, slug = :slug, body = :body, locale = :locale, updated_at = NOW() WHERE id = :id');
        $stmt->execute($data + ['id' => $id]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO pages (title, slug, body, locale, created_at, updated_at) VALUES (:title, :slug, :body, :locale, NOW(), NOW())');
        $stmt->execute($data);
    }

    flash('success', 'Page saved');
    redirect('/admin/pages.php');
}

$pages = $pdo->query('SELECT * FROM pages ORDER BY updated_at DESC')->fetchAll() ?: [];

echo admin_view('pages', [
    'title' => 'Pages',
    'active' => 'pages',
    'pages' => $pages,
    'editing' => $editing,
    'flash' => flash('success'),
]);
