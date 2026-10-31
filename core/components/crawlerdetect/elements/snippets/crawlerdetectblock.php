<?php

/**
 * FormIt preHook: blocks form submission when a bot is detected.
 * Add to &preHooks of FormIt call: crawlerDetectBlock
 * 
 * When called directly [[!crawlerDetectBlock]] returns 0 (human) or 1 (bot) — same as isCrawler.
 * In FormIt context: true = allow, false = block.
 * 
 * @var \MODX\Revolution\modX $modx
 * @var object|null $hook FormIt hook object (if passed).
 */

try {
    $service = $modx->services->get('CrawlerDetect\\CrawlerDetectService');
} catch (\Throwable $e) {
    $modx->log(\MODX\\Revolution\\modX::LOG_LEVEL_ERROR, 'CrawlerDetect: ' . $e->getMessage());
    return isset($hook) ? true : '0';
}

$isBot = $service->isCrawler(null);
$isFormItHook = isset($hook) && is_object($hook);

if (!$isBot) {
    return $isFormItHook ? true : '0';
}

$blockMessage = $modx->getOption('crawlerdetect_block_message', null, 'Could not submit the form. Please try later.');
$logBlocked = (bool) $modx->getOption('crawlerdetect_log_blocked', null, true);

if ($logBlocked) {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $modx->log(\MODX\\Revolution\\modX::LOG_LEVEL_INFO, 'CrawlerDetect: form blocked (bot). User-Agent: ' . substr($ua, 0, 200));
}

if ($isFormItHook && method_exists($hook, 'addError')) {
    $hook->addError('crawlerdetect', $blockMessage);
}
$modx->setPlaceholder('fi.validation_error_message', $blockMessage);

return $isFormItHook ? false : '1';