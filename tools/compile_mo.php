#!/usr/bin/env php
<?php
/**
 * Simple script to compile .po files to .mo files
 * Usage: php compile_mo.php
 */

$locales_dir = dirname(__DIR__) . '/locales';

if (!is_dir($locales_dir)) {
    die("Locales directory not found\n");
}

// Find all .po files
$po_files = glob($locales_dir . '/*.po');

if (empty($po_files)) {
    die("No .po files found\n");
}

foreach ($po_files as $po_file) {
    $mo_file = str_replace('.po', '.mo', $po_file);
    
    echo "Compiling $po_file to $mo_file\n";
    
    // Use msgfmt command if available
    $output = [];
    $return_var = 0;
    
    exec("msgfmt " . escapeshellarg($po_file) . " -o " . escapeshellarg($mo_file), $output, $return_var);
    
    if ($return_var === 0) {
        echo "  ✓ Success\n";
    } else {
        echo "  ✗ Failed (msgfmt might not be installed)\n";
        echo "  Please install gettext tools or compile manually\n";
    }
}

echo "\nDone!\n";
