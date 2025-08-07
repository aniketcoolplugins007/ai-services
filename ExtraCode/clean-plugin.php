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

        // Convert path to glob pattern
        $pattern = rtrim($base_path, '/') . '/' . ltrim($line, '/');

        // Handle ** by converting to GLOB_BRACE syntax
        if (str_contains($line, '**')) {
            $pattern = str_replace('**', '{,*/**}', $pattern);
            $matches = glob($pattern, GLOB_BRACE);
        } else {
            $matches = glob($pattern, GLOB_BRACE);
        }

        if (!$matches) {
            echo "⚠️  No matches for: $line\n";
            continue;
        }

        foreach ($matches as $target_path) {
            if (is_dir($target_path)) {
                rrmdir($target_path);
                echo "🗑️  Deleted folder: $target_path\n";
            } elseif (is_file($target_path)) {
                unlink($target_path);
                echo "🗑️  Deleted file: $target_path\n";
            } else {
                echo "⚠️  Not found or unsupported: $target_path\n";
            }
        }
    }
}

function rrmdir($dir) {
    if (!is_dir($dir)) return;

    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $item;

        is_dir($path) ? rrmdir($path) : unlink($path);
    }
    rmdir($dir);
}

// Usage
$base_path = dirname(__DIR__);
$list_file = $base_path . '/ExtraCode/remove-files-folder.txt';


delete_items_from_list($list_file, $base_path);
