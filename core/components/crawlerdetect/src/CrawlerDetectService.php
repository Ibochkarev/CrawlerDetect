<?php

namespace CrawlerDetect;

use Jaybizzle\CrawlerDetect\CrawlerDetect as CrawlerDetectLib;

/**
 * Единая точка детекции ботов (DRY): используется сниппетом isCrawler и preHook crawlerDetectBlock.
 */
class CrawlerDetectService
{
    /** @var CrawlerDetectLib|null */
    private $detector;

    public function __construct()
    {
        $this->detector = class_exists(CrawlerDetectLib::class)
            ? new CrawlerDetectLib()
            : null;
    }

    /**
     * Проверяет, является ли посетитель ботом.
     *
     * @param string|null $userAgent Строка User-Agent или null для использования из запроса.
     * @return bool true если бот, false если нет или библиотека недоступна (fail-open).
     */
    public function isCrawler(?string $userAgent = null): bool
    {
        if ($this->detector === null) {
            return false;
        }

        if ($this->hasExplicitUserAgent($userAgent)) {
            return $this->detector->isCrawler($userAgent);
        }

        return $this->detector->isCrawler();
    }

    /**
     * Возвращает имя совпадения бота (для отладки/плейсхолдера).
     *
     * @return string Пустая строка если не бот или библиотека недоступна.
     */
    public function getMatches(): string
    {
        if ($this->detector === null) {
            return '';
        }

        $matches = $this->detector->getMatches();

        return is_string($matches) ? $matches : '';
    }

    private function hasExplicitUserAgent(?string $userAgent): bool
    {
        return $userAgent !== null && $userAgent !== '';
    }
}
