<?php

/** @var \MODX\Revolution\modX $modx */
/** @var array $scriptProperties */

$userAgent = $modx->getOption('userAgent', $scriptProperties, null);
if ($userAgent === '') {
    $userAgent = null;
}

try {
    $service = $modx->services->get('CrawlerDetect\\CrawlerDetectService');
} catch (\Throwable $e) {
    $modx->log(\MODX\Revolution\modX::LOG_LEVEL_ERROR, 'CrawlerDetect: ' . $e->getMessage());
    return '0';
}

$isBot = $service->isCrawler($userAgent);
$matches = $service->getMatches();

$placeholderPrefix = $modx->getOption('placeholderPrefix', $scriptProperties, 'crawlerdetect.');
$modx->setPlaceholder($placeholderPrefix . 'matches', $matches);

return $isBot ? '1' : '0';
