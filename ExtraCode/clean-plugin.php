<?php

function delete_items_from_list($file_path, $base_path) {
    if (!file_exists($file_path)) {
        echo "❌ List file not found: $file_path\n";
        return;
    }

    $lines = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;

        $target_path = rtrim($base_path, '/') . '/' . ltrim($line, '/');

        if (str_ends_with($line, '/')) {
            if (is_dir($target_path)) {
                rrmdir($target_path);
                echo "🗑️  Deleted folder: $line\n";
            } else {
                echo "⚠️  Folder not found: $line\n";
            }
        } else {
            if (file_exists($target_path)) {
                unlink($target_path);
                echo "🗑️  Deleted file: $line\n";
            } else {
                echo "⚠️  File not found: $line\n";
            }
        }
    }
}

function rrmdir($dir) {
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = "$dir/$item";
        is_dir($path) ? rrmdir($path) : unlink($path);
    }
    rmdir($dir);
}

// Usage
$base_path = dirname(__DIR__);
$list_file = $base_path . '/ExtraCode/remove-files-folder.txt';


delete_items_from_list($list_file, $base_path);
