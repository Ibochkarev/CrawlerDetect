<?php

/** @var xPDO\Transport\xPDOTransport $transport */
/** @var array $options */
/** @var MODX\Revolution\modX $modx */

if (!$transport->xpdo) {
    return true;
}

$modx = $transport->xpdo;

/** @var MODX\Revolution\modMenu|null $menu */
$menu = $modx->getObject(MODX\Revolution\modMenu::class, ['text' => 'crawlerdetect']);
if ($menu) {
    $menu->remove();
}

return true;
