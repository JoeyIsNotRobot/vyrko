<?php

/**
 * Worktree bootstrap for PHPUnit.
 *
 * Sets APP_BASE_PATH so Laravel uses the worktree's files instead of the
 * main repo's files (since vendor/ is a symlink whose __DIR__ resolves to
 * the main repo, making basePath() point to the wrong location).
 *
 * Also overrides the Composer classmap so PHP classes modified in the
 * worktree are loaded from it instead of the main repo. Composer's classmap
 * takes precedence over PSR-4 prefixes, so we use addClassMap() to inject
 * worktree-specific paths on top of the existing main-repo classmap.
 */
define('LARAVEL_START', microtime(true));

$worktreeRoot = dirname(__DIR__);

// Override the base path so Laravel resolves views, storage, etc. from the worktree.
$_ENV['APP_BASE_PATH'] = $worktreeRoot;
putenv('APP_BASE_PATH=' . $worktreeRoot);

// Locate vendor: may be absent in a git worktree (no symlink created).
// Traverse up to find the real repo root that contains vendor/.
$vendorPath = $worktreeRoot . '/vendor/autoload.php';
if (!file_exists($vendorPath)) {
    $search = dirname($worktreeRoot);
    while ($search !== '/' && $search !== '') {
        $candidate = $search . '/vendor/autoload.php';
        if (file_exists($candidate)) {
            $vendorPath = $candidate;
            break;
        }
        $search = dirname($search);
    }
}

$loader = require $vendorPath;

// When running from a worktree, override classmap entries for all App\ classes
// so worktree files take precedence over main-repo files.
$mainRepoRoot = dirname(dirname($vendorPath));
if (realpath($worktreeRoot) !== realpath($mainRepoRoot)) {
    $classMap = [];

    // Scan worktree app/ directory and build class => file mappings.
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($worktreeRoot . '/app'));
    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }
        $relative = ltrim(str_replace($worktreeRoot . '/app', '', $file->getPathname()), '/');
        $class = 'App\\' . str_replace(['/', '.php'], ['\\', ''], $relative);
        $classMap[$class] = $file->getPathname();
    }

    // Also override Tests\ namespace.
    $testIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($worktreeRoot . '/tests'));
    foreach ($testIterator as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }
        $relative = ltrim(str_replace($worktreeRoot . '/tests', '', $file->getPathname()), '/');
        $class = 'Tests\\' . str_replace(['/', '.php'], ['\\', ''], $relative);
        $classMap[$class] = $file->getPathname();
    }

    // addClassMap merges and later entries override earlier ones (array_merge semantics).
    $loader->addClassMap($classMap);
}
