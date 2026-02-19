<?php

/** @var xPDOTransport $transport */
/** @var array $options */
/** @var modX $modx */

// Симлинки для разработки — только если существует Extras/mCrawlerDetect
$dev = MODX_BASE_PATH . 'Extras/mCrawlerDetect/';
if (!$transport->xpdo || !file_exists($dev)) {
    return true;
}

$modx = $transport->xpdo;
$cache = $modx->getCacheManager();
if (!$cache) {
    return true;
}

$coreLink = $dev . 'core/components/crawlerdetect';
$assetsLink = $dev . 'assets/components/crawlerdetect';

if (!is_link($assetsLink) && file_exists(MODX_ASSETS_PATH . 'components/crawlerdetect/')) {
    @$cache->deleteTree($assetsLink, ['deleteTop' => true, 'skipDirs' => false, 'extensions' => []]);
    @symlink(MODX_ASSETS_PATH . 'components/crawlerdetect/', $assetsLink);
}
if (!is_link($coreLink) && file_exists(MODX_CORE_PATH . 'components/crawlerdetect/')) {
    @$cache->deleteTree($coreLink, ['deleteTop' => true, 'skipDirs' => false, 'extensions' => []]);
    @symlink(MODX_CORE_PATH . 'components/crawlerdetect/', $coreLink);
}

return true;
