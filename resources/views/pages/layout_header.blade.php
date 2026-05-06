<?php
require_once __DIR__ . '/core.php';

// PWA and Face Recognition dependencies - Only output if NOT an AJAX request
if (!isset($_GET['ajax']) && !isset($_POST['ajax']) && !isset($_POST['action'])) {
    echo '<link rel="manifest" href="/manifest.json">';
    echo '<script src="/assets/js/face-api.min.js" defer></script>';
}
?>
