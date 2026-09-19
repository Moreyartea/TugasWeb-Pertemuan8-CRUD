<?php

session_start();

require_once __DIR__ . '/config/database.php';

$db = Database::getInstance()->getConnection();

$search = trim($_GET['search'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 5;
$offset = ($page - 1) * $perPage;

$countSql = "
    SELECT COUNT(*)
    FROM products
    INNER JOIN categories ON products.category_id = categories.id
    INNER JOIN suppliers ON products.supplier_id = suppliers.id
";

if ($search !== '') {
    $countSql .= "
        WHERE products.name LIKE :product_search
        OR categories.name LIKE :category_search
        OR suppliers.name LIKE :supplier_search
    ";
}

$countStmt = $db->prepare($countSql);

if ($search !== '') {
    $searchValue = '%' . $search . '%';

    $countStmt->execute([
        ':product_search' => $searchValue,
        ':category_search' => $searchValue,
        ':supplier_search' => $searchValue
    ]);
} else {
    $countStmt->execute();
}

$totalProducts = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalProducts / $perPage));

if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $perPage;
}

$sql = "
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
";

if ($search !== '') {
    $sql .= "
        WHERE products.name LIKE :product_search
        OR categories.name LIKE :category_search
        OR suppliers.name LIKE :supplier_search
    ";
}

$sql .= "
    ORDER BY products.id DESC
    LIMIT :limit OFFSET :offset
";

$stmt = $db->prepare($sql);

if ($search !== '') {
    $searchValue = '%' . $search . '%';

    $stmt->bindValue(':product_search', $searchValue, PDO::PARAM_STR);
    $stmt->bindValue(':category_search', $searchValue, PDO::PARAM_STR);
    $stmt->bindValue(':supplier_search', $searchValue, PDO::PARAM_STR);
}

$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

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
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

    <div class="container">

        <div class="header">
            <h1>Inventaris Barang</h1>
        </div>

        <?php if ($flash): ?>
            <div class="flash <?= $flash['type'] === 'error' ? 'flash-error' : '' ?>">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <div class="card">

            <div class="toolbar">

                <form method="GET" action="index.php" class="search-form">
                    <input
                        type="text"
                        name="search"
                        placeholder="Cari produk, kategori, atau supplier..."
                        value="<?= htmlspecialchars($search) ?>"
                    >

                    <button type="submit">Cari</button>

                    <?php if ($search !== ''): ?>
                        <a href="index.php" class="btn btn-secondary">Reset</a>
                    <?php endif; ?>
                </form>

                <div>
                    <a href="create.php" class="btn btn-success">Tambah Produk</a>
                    <a href="export.php" class="btn">Export CSV</a>
                </div>

            </div>

            <table>
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
                            <td colspan="7" class="empty">
                                Data produk tidak ditemukan.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $index => $product): ?>
                            <tr>
                                <td><?= $offset + $index + 1 ?></td>

                                <td>
                                    <?= htmlspecialchars($product['name']) ?>
                                </td>

                                <td>
                                    Rp <?= number_format((float) $product['price'], 0, ',', '.') ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars((string) $product['stock']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($product['category_name']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($product['supplier_name']) ?>
                                </td>

                                <td>
                                    <div class="actions">

                                        <a
                                            href="edit.php?id=<?= (int) $product['id'] ?>"
                                            class="btn btn-secondary"
                                        >
                                            Edit
                                        </a>

                                        <form
                                            method="POST"
                                            action="delete.php"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?');"
                                        >
                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?= (int) $product['id'] ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="btn btn-danger"
                                            >
                                                Hapus
                                            </button>
                                        </form>

                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ($totalPages > 1): ?>
                <div class="pagination">

                    <?php if ($page > 1): ?>
                        <a
                            href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>"
                        >
                            Sebelumnya
                        </a>
                    <?php endif; ?>

                    <span>
                        Halaman <?= $page ?> dari <?= $totalPages ?>
                    </span>

                    <?php if ($page < $totalPages): ?>
                        <a
                            href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>"
                        >
                            Berikutnya
                        </a>
                    <?php endif; ?>

                </div>
            <?php endif; ?>

        </div>

    </div>

</body>
</html>