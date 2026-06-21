<?php

/**
 * Worktree bootstrap for PHPUnit.
 *
 * Sets APP_BASE_PATH so Laravel uses the worktree's files instead of the
 * main repo's files (since vendor/ is a symlink whose __DIR__ resolves to
 * the main repo, making basePath() point to the wrong location).
 */
define('LARAVEL_START', microtime(true));

$worktreeRoot = dirname(__DIR__);

// Override the base path so Laravel resolves views, storage, etc. from the worktree
$_ENV['APP_BASE_PATH'] = $worktreeRoot;
putenv('APP_BASE_PATH=' . $worktreeRoot);

require $worktreeRoot . '/vendor/autoload.php';
