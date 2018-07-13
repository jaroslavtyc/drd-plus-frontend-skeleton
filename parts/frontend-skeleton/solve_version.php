<?php
/** @noinspection PhpIncludeInspection */
$autoLoader = require __DIR__ . '/safe_autoload.php';

$currentIndexFile = $documentRoot . '/index.php';
$version = $_GET['version'] ?? $_POST['version'] ?? $_COOKIE['version'] ?? $latestVersion ?? null;
if (!$version || !empty($versionSwitched)) {
    return false;
}
if (PHP_SAPI !== 'cli') {
    \DrdPlus\FrontendSkeleton\TracyDebugger::enable();
}
$webVersionSwitcher = new \DrdPlus\FrontendSkeleton\WebVersionSwitcher(
    new \DrdPlus\FrontendSkeleton\WebVersions($documentRoot),
    $dirs ?? new \DrdPlus\FrontendSkeleton\Dirs($documentRoot),
    new \DrdPlus\FrontendSkeleton\CookiesService()
);
$webVersionSwitcher->persistCurrentVersion($version); // saves required version into cookie
$versionIndexFile = $webVersionSwitcher->getVersionIndexFile($version);
if ($versionIndexFile === $currentIndexFile || \realpath($versionIndexFile) === \realpath($currentIndexFile)) {
    return false;
}
$documentRoot = $webVersionSwitcher->getVersionDocumentRoot($version);
$versionSwitched = $version;
$webVersionSwitcher->persistCurrentVersion($version); // saves required version into cookie
/** @var \Composer\Autoload\ClassLoader $autoLoader */
$autoLoader->unregister(); // as version index will use its own
/** @noinspection PhpIncludeInspection */
require $versionIndexFile;

return true;