# CrawlerDetect

Определение веб-краулеров (ботов) по User-Agent и защита форм от спама без CAPTCHA. Использует [JayBizzle/Crawler-Detect](https://github.com/JayBizzle/Crawler-Detect).

**Требования:** MODX Revolution 3.x, PHP 8.2+

## Установка

Установите пакет через **Менеджер пакетов** MODX. Зависимости уже входят в пакет. `composer install` на сервере не требуется.

## Быстрый старт

### MODX-теги

1. **Защита формы:** добавьте `crawlerDetectBlock` в preHooks FormIt:
   ```modx
   [[!FormIt? &preHooks=`crawlerDetectBlock` &hooks=`email,redirect` ...]]
   ```

2. **Скрыть виджет от ботов:** вызывайте только не-ботам:
   ```modx
   [[!isCrawler:eq=`0`:then=`[[$chatWidget]]`]]
   ```

### Fenom

Сниппеты вызываются через `$modx->runSnippet()`; не кэшируйте вызов `isCrawler` на стороне страницы, если нужна актуальная проверка каждого запроса (аналог `[[!...]]`).

1. **FormIt с preHook `crawlerDetectBlock`:**
   ```fenom
   {$modx->runSnippet('FormIt', [
      'preHooks' => 'crawlerDetectBlock',
      'hooks' => 'email,redirect',
      'validate' => 'name:required,email:required:email',
      'redirectTo' => $modx->resource->id,
      'emailTo' => $modx->getOption('emailsender'),
      'emailSubject' => 'Обратная связь'
   ])}
   {if $modx->getPlaceholder('fi.validation_error_message')}
      <div class="error">{$modx->getPlaceholder('fi.validation_error_message')}</div>
   {/if}
   <form action="{$modx->makeUrl($modx->resource->id)}" method="post">
      <input type="text" name="name" value="{$modx->getPlaceholder('fi.name')}" />
      <input type="email" name="email" value="{$modx->getPlaceholder('fi.email')}" />
      <button type="submit" name="submit">Отправить</button>
   </form>
   ```

2. **Виджет только для людей (не ботам):**
   ```fenom
   {if $modx->runSnippet('isCrawler', []) == '0'}
      {$modx->getChunk('chatWidget')}
   {/if}
   ```

3. **Проверка с кастомным User-Agent и именем бота** (плейсхолдер `crawlerdetect.matches` выставляет сниппет):
   ```fenom
   {$modx->runSnippet('isCrawler', ['userAgent' => $custom_user_agent])}
   {if $modx->getPlaceholder('crawlerdetect.matches')}
      Обнаружен бот: {$modx->getPlaceholder('crawlerdetect.matches')}
   {/if}
   ```

Больше вариантов и нюансов — в [API](core/components/crawlerdetect/docs/api.md).

## Документация

- [Руководство пользователя](core/components/crawlerdetect/docs/user-guide.md) — установка, сценарии, FAQ
- [API](core/components/crawlerdetect/docs/api.md) — спецификация сниппетов, параметры
- [Справка](core/components/crawlerdetect/docs/readme.txt) — краткое описание

## Сборка (для разработчиков)

Сборка пакета: `php _build/build.php` или через браузер: `http://site/Extras/mCrawlerDetect/_build/build.php`

Скачать transport.zip: добавьте `?download=1` к URL.

Настройки: `_build/config.inc.php`