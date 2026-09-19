<?php

require_once __DIR__ . '/config/database.php';

$db = Database::getInstance()->getConnection();

echo 'Koneksi database berhasil.';