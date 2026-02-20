<?php

/**
 * FormIt preHook: блокирует отправку формы при обнаружении бота.
 * Добавьте в &preHooks вызова FormIt: crawlerDetectBlock
 *
 * При прямом вызове [[!crawlerDetectBlock]] возвращает 0 (человек) или 1 (бот) — как isCrawler.
 * В контексте FormIt: true = разрешить, false = заблокировать.
 *
 * @var \MODX\Revolution\modX $modx
 * @var object|null $hook Объект хука FormIt (если передан).
 */

try {
    $service = $modx->services->get('CrawlerDetect\\CrawlerDetectService');
} catch (\Throwable $e) {
    $modx->log(\MODX\Revolution\modX::LOG_LEVEL_ERROR, 'CrawlerDetect: ' . $e->getMessage());
    return isset($hook) ? true : '0';
}

$isBot = $service->isCrawler(null);
$isFormItHook = isset($hook) && is_object($hook);

if (!$isBot) {
    return $isFormItHook ? true : '0';
}

$blockMessage = $modx->getOption('crawlerdetect_block_message', null, 'Не удалось отправить форму. Попробуйте позже.');
$logBlocked = (bool) $modx->getOption('crawlerdetect_log_blocked', null, false);

if ($logBlocked) {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $modx->log(\MODX\Revolution\modX::LOG_LEVEL_INFO, 'CrawlerDetect: форма заблокирована (бот). User-Agent: ' . substr($ua, 0, 200));
}

if ($isFormItHook && method_exists($hook, 'addError')) {
    $hook->addError('crawlerdetect', $blockMessage);
}
$modx->setPlaceholder('fi.validation_error_message', $blockMessage);

return $isFormItHook ? false : '1';