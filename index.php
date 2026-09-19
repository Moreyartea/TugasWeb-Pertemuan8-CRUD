<?php

session_start();

require_once __DIR__ . '/config/database.php';

$db = Database::getInstance()->getConnection();

$stmt = $db->prepare("
    SELECT
        products.id,
        products.name,
        products.price,
        products.stock,
        categories.name AS category_name,
        suppliers.name AS supplier_name
    FROM products
    INNER JOIN categories ON products.category_id = categories.id
    INNER JOIN suppliers ON products.supplier_id = suppliers.id
    ORDER BY products.id DESC
");

$stmt->execute();

$products = $stmt->fetchAll();

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventaris Barang</title>
</head>
<body>

    <h1>Inventaris Barang</h1>

    <?php if ($flash): ?>
        <p><?= htmlspecialchars($flash['message']) ?></p>
    <?php endif; ?>

    <p>
        <a href="create.php">Tambah Produk</a>
    </p>

    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Produk</th>
                <th>Harga</th>
                <th>Stok</th>
                <th>Kategori</th>
                <th>Supplier</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$products): ?>
                <tr>
                    <td colspan="7">Belum ada data produk.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($products as $index => $product): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($product['name']) ?></td>
                        <td>Rp <?= number_format((float) $product['price'], 0, ',', '.') ?></td>
                        <td><?= htmlspecialchars((string) $product['stock']) ?></td>
                        <td><?= htmlspecialchars($product['category_name']) ?></td>
                        <td><?= htmlspecialchars($product['supplier_name']) ?></td>
                        <td>
                            <a href="edit.php?id=<?= (int) $product['id'] ?>">Edit</a>

                            <form
                                method="POST"
                                action="delete.php"
                                style="display: inline;"
                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?');"
                            >
                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= (int) $product['id'] ?>"
                                >
                                <button type="submit">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>