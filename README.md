# CrawlerDetect

Определение веб-краулеров (ботов) по User-Agent и защита форм от спама без CAPTCHA. Использует [JayBizzle/Crawler-Detect](https://github.com/JayBizzle/Crawler-Detect).

**Требования:** MODX Revolution 3.x, PHP 8.2+

## Установка

Установите пакет через **Менеджер пакетов** MODX. Зависимости уже входят в пакет. `composer install` на сервере не требуется.

## Быстрый старт

1. **Защита формы:** добавьте `crawlerDetectBlock` в preHooks FormIt:
   ```modx
   [[!FormIt? &preHooks=`crawlerDetectBlock` &hooks=`email,redirect` ...]]
   ```

2. **Скрыть виджет от ботов:** вызывайте только не-ботам:
   ```modx
   [[!isCrawler:eq=`0`:then=`[[$chatWidget]]`]]
   ```

## Документация

- [Руководство пользователя](core/components/crawlerdetect/docs/user-guide.md) — установка, сценарии, FAQ
- [API](core/components/crawlerdetect/docs/api.md) — спецификация сниппетов, параметры
- [Справка](core/components/crawlerdetect/docs/readme.txt) — краткое описание

## Сборка (для разработчиков)

Сборка пакета: `php _build/build.php` или через браузер: `http://site/Extras/mCrawlerDetect/_build/build.php`

Скачать transport.zip: добавьте `?download=1` к URL.

Настройки: `_build/config.inc.php`