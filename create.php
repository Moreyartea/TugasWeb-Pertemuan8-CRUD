<?php

session_start();

require_once __DIR__ . '/config/database.php';

$db = Database::getInstance()->getConnection();

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
            INSERT INTO products (
                name,
                price,
                stock,
                category_id,
                supplier_id
            ) VALUES (
                :name,
                :price,
                :stock,
                :category_id,
                :supplier_id
            )
        ");

        $stmt->execute([
            ':name' => $name,
            ':price' => $price,
            ':stock' => $stock,
            ':category_id' => $categoryId,
            ':supplier_id' => $supplierId
        ]);

        $_SESSION['flash'] = [
            'type' => 'success',
            'message' => 'Produk berhasil ditambahkan.'
        ];

        header('Location: index.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

    <div class="container">

        <div class="header">
            <h1>Tambah Produk</h1>
        </div>

        <div class="card">

            <?php if ($errors): ?>
                <div class="flash flash-error">
                    <?php foreach ($errors as $error): ?>
                        <div><?= htmlspecialchars($error) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">

                <div class="form-group">
                    <label for="name">Nama Produk</label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="price">Harga</label>

                    <input
                        type="number"
                        id="price"
                        name="price"
                        min="0"
                        step="0.01"
                        value="<?= htmlspecialchars($_POST['price'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="stock">Stok</label>

                    <input
                        type="number"
                        id="stock"
                        name="stock"
                        min="0"
                        value="<?= htmlspecialchars($_POST['stock'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="category_id">Kategori</label>

                    <select
                        id="category_id"
                        name="category_id"
                        required
                    >
                        <option value="">-- Pilih Kategori --</option>

                        <?php foreach ($categories as $category): ?>
                            <option
                                value="<?= (int) $category['id'] ?>"
                                <?= (int) ($_POST['category_id'] ?? 0) === (int) $category['id'] ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="supplier_id">Supplier</label>

                    <select
                        id="supplier_id"
                        name="supplier_id"
                        required
                    >
                        <option value="">-- Pilih Supplier --</option>

                        <?php foreach ($suppliers as $supplier): ?>
                            <option
                                value="<?= (int) $supplier['id'] ?>"
                                <?= (int) ($_POST['supplier_id'] ?? 0) === (int) $supplier['id'] ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($supplier['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-success">
                        Simpan Produk
                    </button>

                    <a href="index.php" class="btn btn-secondary">
                        Kembali
                    </a>
                </div>

            </form>

        </div>

    </div>

</body>
</html>