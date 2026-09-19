<?php

session_start();

require_once __DIR__ . '/config/database.php';

$db = Database::getInstance()->getConnection();

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['flash'] = [
        'type' => 'error',
        'message' => 'ID produk tidak valid.'
    ];

    header('Location: index.php');
    exit;
}

$stmt = $db->prepare("
    SELECT id, name, price, stock, category_id, supplier_id
    FROM products
    WHERE id = :id
");

$stmt->execute([
    ':id' => $id
]);

$product = $stmt->fetch();

if (!$product) {
    $_SESSION['flash'] = [
        'type' => 'error',
        'message' => 'Produk tidak ditemukan.'
    ];

    header('Location: index.php');
    exit;
}

$categories = $db->query("
    SELECT id, name
    FROM categories
    ORDER BY name ASC
")->fetchAll();

$suppliers = $db->query("
    SELECT id, name
    FROM suppliers
    ORDER BY name ASC
")->fetchAll();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $price = (float) ($_POST['price'] ?? 0);
    $stock = (int) ($_POST['stock'] ?? 0);
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $supplierId = (int) ($_POST['supplier_id'] ?? 0);

    if ($name === '') {
        $errors[] = 'Nama produk wajib diisi.';
    }

    if ($price < 0) {
        $errors[] = 'Harga tidak boleh kurang dari 0.';
    }

    if ($stock < 0) {
        $errors[] = 'Stok tidak boleh kurang dari 0.';
    }

    if ($categoryId <= 0) {
        $errors[] = 'Kategori wajib dipilih.';
    }

    if ($supplierId <= 0) {
        $errors[] = 'Supplier wajib dipilih.';
    }

    if (!$errors) {
        $stmt = $db->prepare("
            UPDATE products
            SET
                name = :name,
                price = :price,
                stock = :stock,
                category_id = :category_id,
                supplier_id = :supplier_id
            WHERE id = :id
        ");

        $stmt->execute([
            ':name' => $name,
            ':price' => $price,
            ':stock' => $stock,
            ':category_id' => $categoryId,
            ':supplier_id' => $supplierId,
            ':id' => $id
        ]);

        $_SESSION['flash'] = [
            'type' => 'success',
            'message' => 'Produk berhasil diperbarui.'
        ];

        header('Location: index.php');
        exit;
    }

    $product['name'] = $name;
    $product['price'] = $price;
    $product['stock'] = $stock;
    $product['category_id'] = $categoryId;
    $product['supplier_id'] = $supplierId;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk</title>
</head>
<body>

    <h1>Edit Produk</h1>

    <?php if ($errors): ?>
        <div>
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div>
            <label for="name">Nama Produk</label>
            <input
                type="text"
                id="name"
                name="name"
                value="<?= htmlspecialchars($product['name']) ?>"
                required
            >
        </div>

        <div>
            <label for="price">Harga</label>
            <input
                type="number"
                id="price"
                name="price"
                min="0"
                step="0.01"
                value="<?= htmlspecialchars((string) $product['price']) ?>"
                required
            >
        </div>

        <div>
            <label for="stock">Stok</label>
            <input
                type="number"
                id="stock"
                name="stock"
                min="0"
                value="<?= htmlspecialchars((string) $product['stock']) ?>"
                required
            >
        </div>

        <div>
            <label for="category_id">Kategori</label>
            <select id="category_id" name="category_id" required>
                <option value="">-- Pilih Kategori --</option>

                <?php foreach ($categories as $category): ?>
                    <option
                        value="<?= (int) $category['id'] ?>"
                        <?= (int) $product['category_id'] === (int) $category['id'] ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="supplier_id">Supplier</label>
            <select id="supplier_id" name="supplier_id" required>
                <option value="">-- Pilih Supplier --</option>

                <?php foreach ($suppliers as $supplier): ?>
                    <option
                        value="<?= (int) $supplier['id'] ?>"
                        <?= (int) $product['supplier_id'] === (int) $supplier['id'] ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($supplier['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit">Simpan Perubahan</button>
        <a href="index.php">Kembali</a>
    </form>

</body>
</html>