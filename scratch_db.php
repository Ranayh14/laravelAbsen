<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=laravel_absen_db;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->query("SELECT id, nim, nama, face_embedding IS NOT NULL as has_embedding, foto_base64 IS NOT NULL as has_foto FROM users WHERE role='pegawai'");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
