<?php

require_once __DIR__ . '/config/database.php';

$db = Database::getInstance()->getConnection();

$stmt = $db->prepare("
    SELECT
        products.id,
        products.name,
        products.price,
        products.stock,
        categories.name AS category_name,
        suppliers.name AS supplier_name,
        products.created_at,
        products.updated_at
    FROM products
    INNER JOIN categories ON products.category_id = categories.id
    INNER JOIN suppliers ON products.supplier_id = suppliers.id
    ORDER BY products.id ASC
");

$stmt->execute();

$products = $stmt->fetchAll();

$filename = 'laporan-inventaris-' . date('Y-m-d-H-i-s') . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');

fwrite($output, "\xEF\xBB\xBF");

fputcsv($output, [
    'ID',
    'Nama Produk',
    'Harga',
    'Stok',
    'Kategori',
    'Supplier',
    'Dibuat',
    'Diperbarui'
]);

foreach ($products as $product) {
    fputcsv($output, [
        $product['id'],
        $product['name'],
        $product['price'],
        $product['stock'],
        $product['category_name'],
        $product['supplier_name'],
        $product['created_at'],
        $product['updated_at']
    ]);
}

fclose($output);
exit;