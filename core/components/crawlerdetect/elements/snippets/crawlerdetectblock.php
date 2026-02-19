<?php

/**
 * FormIt preHook: блокирует отправку формы при обнаружении бота.
 * Добавьте в &preHooks вызова FormIt: crawlerDetectBlock
 *
 * @var \MODX\Revolution\modX $modx
 * @var object|null $hook Объект хука FormIt (если передан).
 */

try {
    $service = $modx->services->get('CrawlerDetect\\CrawlerDetectService');
} catch (\Throwable $e) {
    $modx->log(\MODX\Revolution\modX::LOG_LEVEL_ERROR, 'CrawlerDetect: ' . $e->getMessage());
    return true;
}

if (!$service->isCrawler(null)) {
    return true;
}

$blockMessage = $modx->getOption('crawlerdetect_block_message', null, 'Не удалось отправить форму. Попробуйте позже.');
$logBlocked = (bool) $modx->getOption('crawlerdetect_log_blocked', null, false);

if ($logBlocked) {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $modx->log(\MODX\Revolution\modX::LOG_LEVEL_INFO, 'CrawlerDetect: форма заблокирована (бот). User-Agent: ' . substr($ua, 0, 200));
}

if (isset($hook) && is_object($hook) && method_exists($hook, 'addError')) {
    $hook->addError('crawlerdetect', $blockMessage);
}
$modx->setPlaceholder('fi.validation_error_message', $blockMessage);

return false;