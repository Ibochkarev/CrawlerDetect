<?php

/**
 * @var \MODX\Revolution\modX $modx
 * @var array $namespace
 */

$componentPath = $namespace['path'];
$vendorPath = $componentPath . 'vendor/';

if (file_exists($vendorPath . 'autoload.php')) {
    require_once $vendorPath . 'autoload.php';
}

$modx->addPackage('CrawlerDetect\\', $componentPath . 'src/', null, 'CrawlerDetect\\');

$modx->services->add('CrawlerDetect\\CrawlerDetectService', function ($c) {
    return new \CrawlerDetect\CrawlerDetectService();
});
