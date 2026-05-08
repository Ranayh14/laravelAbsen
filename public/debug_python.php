<?php
// Test script to check python visibility
header('Content-Type: text/plain');

$pythonPath = 'C:\\Python313\\python.exe';
echo "Checking Python at: $pythonPath\n";

if (file_exists($pythonPath)) {
    echo "SUCCESS: File exists.\n";
} else {
    echo "ERROR: File NOT found at this path.\n";
}

echo "\nTrying to run 'python --version' via shell_exec:\n";
$output = shell_exec('python --version 2>&1');
echo "Output: " . ($output ?: "NULL (No output)") . "\n";

echo "\nTrying to run absolute path via shell_exec:\n";
$outputAbs = shell_exec('"' . $pythonPath . '" --version 2>&1');
echo "Output: " . ($outputAbs ?: "NULL (No output)") . "\n";

echo "\nPHP User: " . get_current_user() . "\n";
?>
