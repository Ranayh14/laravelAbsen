<?php
header('Content-Type: application/json');
try {
    $pdo = new PDO("mysql:host=localhost;dbname=laravel_absen_db;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $userId = $_POST['user_id'] ?? null;
        $embedding = $_POST['embedding'] ?? null;
        
        if ($userId && $embedding) {
            $stmt = $pdo->prepare("UPDATE users SET face_embedding = ?, face_embedding_updated = NOW() WHERE id = ?");
            if ($stmt->execute([$embedding, $userId])) {
                echo json_encode(["success" => true]);
                exit;
            }
        }
        echo json_encode(["success" => false, "error" => "Invalid POST data"]);
        exit;
    }
    
    // GET: Return users
    $stmt = $pdo->query("SELECT id, nim, nama, foto_base64, face_embedding FROM users WHERE role='pegawai'");
    echo json_encode(["data" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
