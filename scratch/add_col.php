<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

if (!\Schema::hasColumn('users', 'face_embedding_128')) {
    \Schema::table('users', function($table) {
        $table->text('face_embedding_128')->nullable();
    });
    echo "Column face_embedding_128 added.\n";
} else {
    echo "Column face_embedding_128 already exists.\n";
}
