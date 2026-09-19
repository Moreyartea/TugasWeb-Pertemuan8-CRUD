<?php

session_start();

require_once __DIR__ . '/config/database.php';

$db = Database::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash'] = [
        'type' => 'error',
        'message' => 'Metode permintaan tidak valid.'
    ];

    header('Location: index.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['flash'] = [
        'type' => 'error',
        'message' => 'ID produk tidak valid.'
    ];

    header('Location: index.php');
    exit;
}

try {
    $db->beginTransaction();

    $stmt = $db->prepare("
        SELECT id, name
        FROM products
        WHERE id = :id
        FOR UPDATE
    ");

    $stmt->execute([
        ':id' => $id
    ]);

    $product = $stmt->fetch();

    if (!$product) {
        throw new RuntimeException('Produk tidak ditemukan.');
    }

    $stmt = $db->prepare("
        DELETE FROM products
        WHERE id = :id
    ");

    $stmt->execute([
        ':id' => $id
    ]);

    $stmt = $db->prepare("
        INSERT INTO activity_logs (
            product_id,
            action,
            description
        ) VALUES (
            :product_id,
            :action,
            :description
        )
    ");

    $stmt->execute([
        ':product_id' => $product['id'],
        ':action' => 'DELETE',
        ':description' => 'Produk "' . $product['name'] . '" telah dihapus.'
    ]);

    $db->commit();

    $_SESSION['flash'] = [
        'type' => 'success',
        'message' => 'Produk berhasil dihapus.'
    ];
} catch (Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }

    $_SESSION['flash'] = [
        'type' => 'error',
        'message' => 'Produk gagal dihapus.'
    ];
}

header('Location: index.php');
exit;