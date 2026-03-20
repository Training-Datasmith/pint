<?php

declare(strict_types=1);

/**
 * Example: Using Laravel Pint for code style fixing.
 *
 * Install:
 *   composer require --dev laravel/pint
 *
 * Pint is a CLI tool; this file documents common usage patterns.
 * Run from your project root.
 */

// --- Basic usage (fix all PHP files in the project) ---
// vendor/bin/pint

// --- Fix specific paths ---
// vendor/bin/pint app/ tests/

// --- Preview changes without writing (dry run) ---
// vendor/bin/pint --test

// --- Fix only files changed since the last git commit ---
// vendor/bin/pint --dirty

// --- Use a specific preset ---
// vendor/bin/pint --preset psr12

// --- Output format ---
// vendor/bin/pint --format json

// --- Configure via pint.json in your project root ---
// {
//   "preset": "laravel",
//   "rules": {
//     "ordered_imports": true,
//     "no_unused_imports": true,
//     "binary_operator_spaces": {
//       "default": "align_single_space_minimal"
//     }
//   },
//   "exclude": [
//     "storage",
//     "bootstrap/cache"
//   ]
// }

// --- Parallel mode (for large codebases) ---
// vendor/bin/pint --parallel

// --- CI integration: exit with non-zero status if any files need fixing ---
// vendor/bin/pint --test && echo "Code style OK" || exit 1
