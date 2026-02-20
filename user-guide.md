# Руководство пользователя CrawlerDetect

**Версия:** 1.0.0-pl  
**Для:** MODX Revolution 3.x

---

## Содержание

1. [Введение](#1-введение)
2. [Быстрый старт](#2-быстрый-старт)
3. [Установка](#3-установка)
4. [Защита форм от спама](#4-защита-форм-от-спама)
5. [Скрытие контента от ботов](#5-скрытие-контента-от-ботов)
6. [Настройка](#6-настройка)
7. [Типовые сценарии](#7-типовые-сценарии)
8. [Решение проблем](#8-решение-проблем)
9. [Часто задаваемые вопросы](#9-часто-задаваемые-вопросы)
10. [Ссылки](#10-ссылки)

---

## 1. Введение

### Что такое CrawlerDetect?

CrawlerDetect — дополнение для MODX, которое определяет, является ли посетитель сайта ботом (поисковый робот, парсер, спам-бот) по заголовку User-Agent. Это позволяет:

- **Защищать формы** — блокировать отправку форм ботами без CAPTCHA
- **Скрывать виджеты** — не показывать чат, аналитику и тяжёлые скрипты ботам
- **Точнее считать посетителей** — исключать ботов из счётчиков «онлайн» и «просмотров»

### Для кого эта документация

| Роль | Что найдёте |
|------|-------------|
| **Владелец сайта** | Защита форм, настройка сообщений, меньше спама |
| **Разработчик** | Примеры кода (MODX и Fenom), интеграция с FormIt, FetchIt и SendIt |
| **Маркетолог** | Корректная аналитика без ботов |

### Требования

- MODX Revolution 3.x
- PHP 8.2+ (минимальная версия для MODX 3)
- FormIt (для защиты форм)
- FetchIt (опционально, для AJAX-форм)
- SendIt (опционально, для AJAX-форм)

---

## 2. Быстрый старт

### За 2 минуты: защитить контактную форму

**Шаг 1.** Откройте страницу с формой в редакторе MODX.

**Шаг 2.** Найдите вызов FormIt и добавьте в него `&preHooks=\`crawlerDetectBlock\``:

**MODX (теги):**
```modx
[[!FormIt?
    &preHooks=`crawlerDetectBlock`
    &hooks=`email,redirect`
    &validate=`name:required,email:required:email`
    &redirectTo=`[[*id]]`
    &emailTo=`[[++emailsender]]`
    &emailSubject=`Обратная связь`
]]
[[+fi.validation_error_message]]
<form action="[[~[[*id]]]]" method="post">
    <input type="text" name="name" value="[[+fi.name]]" />
    <input type="email" name="email" value="[[+fi.email]]" />
    <button type="submit" name="submit">Отправить</button>
</form>
```

**Fenom:**
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

**Шаг 3.** Сохраните страницу. Форма защищена от ботов.

---

## 3. Установка

### Установка через Менеджер пакетов

1. Управление пакетами → **Установить пакеты**
2. Найдите **CrawlerDetect** в репозитории
3. Нажмите **Установить**

**Важно:** Зависимости (библиотека JayBizzle/Crawler-Detect) уже входят в пакет. Запускать `composer install` на сервере **не нужно**.

### Проверка установки

После установки в меню «Элементы» → «Сниппеты» должны появиться:

- `isCrawler` — определение бота
- `crawlerDetectBlock` — preHook для FormIt

---

## 4. Защита форм от спама

### Как это работает

1. Пользователь отправляет форму
2. FormIt вызывает preHook `crawlerDetectBlock` **до** валидации и отправки
3. Если User-Agent — бот → форма не обрабатывается, показывается сообщение
4. Если человек → форма обрабатывается как обычно

### Обычная форма (FormIt)

Добавьте `crawlerDetectBlock` в `&preHooks` вызова FormIt. Если у вас уже есть другие preHooks, перечислите их через запятую:

**MODX:**
```modx
&preHooks=`crawlerDetectBlock,другойХук`
```

**Fenom:**
```fenom
'preHooks' => 'crawlerDetectBlock,другойХук'
```

### AJAX-форма (FetchIt)

FetchIt обрабатывает формы через FormIt на сервере. Чтобы защитить форму:

1. В конфигурации FetchIt укажите URL/сниппет, который вызывает FormIt
2. В вызов FormIt добавьте `&preHooks="crawlerDetectBlock"`
3. При блокировке ботом FetchIt получит ответ с ошибкой и покажет сообщение из настроек CrawlerDetect

**Пример:** если FetchIt отправляет данные на `[[~123]]` (страница с FormIt), убедитесь, что на этой странице FormIt вызывается с `crawlerDetectBlock` в preHooks.

### AJAX-форма (SendIt)

SendIt обрабатывает формы через FormIt; параметры вызова задаются в пресетах (файл из настройки **si_path_to_presets**). Чтобы защитить форму:

1. Откройте файл пресетов (свою копию, не стандартный `core/components/sendit/presets/sendit.inc.php` — при обновлении SendIt он перезаписывается).
2. Добавьте в нужный пресет `preHooks` с `crawlerDetectBlock`.
3. При блокировке ботом SendIt вернёт ошибку и покажет сообщение из настроек CrawlerDetect.

**Пример пресета:**
```php
return [
    'contact' => [
        'preHooks' => 'crawlerDetectBlock',
        'hooks' => 'email,FormItSaveForm',
        'validate' => 'name:required,email:email:required',
        'emailTo' => 'manager@site.ru',
        'emailSubject' => 'Обратная связь',
        // ...
    ],
];
```

Если preHooks уже есть, перечислите через запятую: `'preHooks' => 'crawlerDetectBlock,другойХук'`.

### Сообщение при блокировке

Текст сообщения настраивается в **Системные настройки** → `crawlerdetect` → `crawlerdetect_block_message`.

По умолчанию: «Не удалось отправить форму. Попробуйте позже.»

Вывод сообщения: через плейсхолдер FormIt `[[+fi.validation_error_message]]` (MODX) или `{$modx->getPlaceholder('fi.validation_error_message')}` (Fenom).

---

## 5. Скрытие контента от ботов

### Сниппет isCrawler

Определяет, является ли текущий посетитель ботом.

- **Возвращает:** `"1"` (бот) или `"0"` (не бот)
- **Важно:** вызывайте без кэша: `[[!isCrawler]]`

### Показать виджет только людям

**MODX:**
```modx
[[!isCrawler:eq=`0`:then=`[[$chatWidget]]`]]
```

**Fenom:**
```fenom
{if $modx->runSnippet('isCrawler', []) == '0'}
    {$modx->getChunk('chatWidget')}
{/if}
```

### Не подключать аналитику ботам

**MODX:**
```modx
[[!isCrawler:eq=`0`:then=`[[$googleAnalytics]]`]]
```

**Fenom:**
```fenom
{if $modx->runSnippet('isCrawler', []) == '0'}
    {$modx->getChunk('googleAnalytics')}
{/if}
```

### Разный контент для бота и человека

**MODX:**
```modx
[[!isCrawler:eq=`0`:then=`[[$fullContent]]`:else=`[[$liteContent]]`]]
```

**Fenom:**
```fenom
{set $isBot = $modx->runSnippet('isCrawler', [])}
{if $isBot == '0'}
    {$modx->getChunk('fullContent')}
{else}
    {$modx->getChunk('liteContent')}
{/if}
```

### Отладка: какой бот обнаружен

При обнаружении бота в плейсхолдер `crawlerdetect.matches` записывается имя (например, `Googlebot`):

**MODX:**
```modx
[[!isCrawler]]
[[+crawlerdetect.matches]]
```

**Fenom:**
```fenom
{$modx->runSnippet('isCrawler', [])}
{if $modx->getPlaceholder('crawlerdetect.matches')}
    Бот: {$modx->getPlaceholder('crawlerdetect.matches')}
{/if}
```

---

## 6. Настройка

### Системные настройки (namespace: crawlerdetect)

| Настройка | Описание | По умолчанию |
|-----------|----------|--------------|
| `crawlerdetect_block_message` | Текст при блокировке формы ботом | «Не удалось отправить форму. Попробуйте позже.» |
| `crawlerdetect_log_blocked` | Логировать заблокированные отправки в лог MODX | Да |

### Где изменить

**Система** → **Системные настройки** → фильтр по namespace `crawlerdetect`

### Свойства сниппета isCrawler

| Свойство | Описание | По умолчанию |
|----------|----------|--------------|
| `userAgent` | Строка User-Agent для проверки (если пусто — из запроса) | — |
| `placeholderPrefix` | Префикс плейсхолдера для имени бота | `crawlerdetect.` |

---

## 7. Типовые сценарии

### Сценарий 1: Контактная форма

Защита одной контактной формы — добавьте `crawlerDetectBlock` в preHooks FormIt (см. [Быстрый старт](#2-быстрый-старт)).

### Сценарий 2: Несколько форм на сайте

Один и тот же preHook можно использовать для всех форм. В каждой форме добавьте `crawlerDetectBlock` в `&preHooks` вызова FormIt.

### Сценарий 3: Счётчик «N человек на сайте»

Вызывайте сниппет счётчика только когда посетитель не бот:

**MODX:**
```modx
[[!isCrawler:eq=`0`:then=`[[!yourVisitorCounterSnippet]]`]]
```

**Fenom:**
```fenom
{if $modx->runSnippet('isCrawler', []) == '0'}
    {$modx->runSnippet('yourVisitorCounterSnippet', [])}
{/if}
```

### Сценарий 4: Форма «Заказать звонок» (FetchIt)

1. Убедитесь, что FetchIt настроен на вызов FormIt на сервере
2. В FormIt на странице назначения добавьте `&preHooks="crawlerDetectBlock"`
3. При блокировке ботом FetchIt покажет сообщение из настроек CrawlerDetect

### Сценарий 5: E-commerce — «Смотрят этот товар»

Не учитывать ботов в счётчике просмотров товара:

**MODX:**
```modx
[[!isCrawler:eq=`0`:then=`[[!msProductViews? &id=`[[*id]]`]]`]]
```

**Fenom:**
```fenom
{if $modx->runSnippet('isCrawler', []) == '0'}
    {$modx->runSnippet('msProductViews', ['id' => $productId])}
{/if}
```

---

## 8. Решение проблем

### Форма не блокируется ботами

**Проверьте:**

1. `crawlerDetectBlock` указан в `&preHooks` вызова FormIt
2. Форма отправляется через FormIt (не через другой обработчик)
3. Для FetchIt — FormIt на сервере вызывается с `crawlerDetectBlock`

**Проверка:** отправьте форму с User-Agent бота (например, `Googlebot`) через инструменты разработчика (изменение заголовков) или через curl.

### Сниппет isCrawler всегда возвращает 0

**Возможные причины:**

1. **Кэширование** — убедитесь, что используете `[[!isCrawler]]` (без кэша)
2. **Библиотека не загружена** — проверьте наличие `core/components/crawlerdetect/vendor/autoload.php`; при ошибке загрузки сниппет возвращает `0` (fail-open) и пишет в лог MODX

### Сообщение при блокировке не показывается

**Проверьте:**

1. В шаблоне формы выводится `[[+fi.validation_error_message]]` (MODX) или `{$modx->getPlaceholder('fi.validation_error_message')}` (Fenom)
2. Плейсхолдер не перезаписывается другими хуками

### Ложные срабатывания (человека блокируют)

**Редко**, но возможно при нестандартном User-Agent. Решение:

1. Временно отключите `crawlerDetectBlock` или проверьте логи
2. Сообщите о проблеме (User-Agent) в репозиторий CrawlerDetect; библиотека JayBizzle/Crawler-Detect регулярно обновляется

### Просмотр логов

**Управление** → **Системный журнал** — при включённой настройке `crawlerdetect_log_blocked` заблокированные попытки будут в логе.

---

## 9. Часто задаваемые вопросы

### Нужно ли запускать composer install на сервере?

**Нет.** Зависимости уже входят в пакет. Установите CrawlerDetect через Менеджер пакетов — этого достаточно.

### Как обновить библиотеку JayBizzle/Crawler-Detect?

Обновляйте пакет CrawlerDetect через Менеджер пакетов. Новая версия дополнения содержит актуальную версию библиотеки. Отдельно обновлять библиотеку на сервере не нужно.

### Совместим ли CrawlerDetect с CAPTCHA?

Да. CrawlerDetect можно использовать вместе с reCAPTCHA или другими CAPTCHA: добавьте оба preHook в FormIt.

**MODX:** `&preHooks=\`crawlerDetectBlock,recaptcha\``

**Fenom:** `'preHooks' => 'crawlerDetectBlock,recaptcha'`

CrawlerDetect сработает первым и отсечёт ботов до CAPTCHA.

### Работает ли с AjaxForm?

AjaxForm — альтернатива FormIt. CrawlerDetect интегрирован с FormIt. Если AjaxForm вызывает FormIt на сервере, добавьте `crawlerDetectBlock` в preHooks FormIt — защита будет работать.

### Работает ли с SendIt?

Да. SendIt использует FormIt; параметры задаются в пресетах. Добавьте в пресет `'preHooks' => 'crawlerDetectBlock'` — при блокировке ботом SendIt вернёт ошибку и покажет сообщение из настроек CrawlerDetect (см. [Защита форм от спама → SendIt](#ajax-форма-sendit)).

### Поддерживается ли MODX 2.x?

Нет. Только MODX Revolution 3.x.

### Можно ли добавить свой User-Agent в чёрный список?

В текущей версии — нет. Используется только библиотека JayBizzle/Crawler-Detect. Расширение чёрного/белого списка — в планах на будущие версии.

---

## 10. Ссылки

- [JayBizzle/Crawler-Detect](https://github.com/JayBizzle/Crawler-Detect) — библиотека детекции
- [FormIt (MODX 3.x)](https://docs.modx.com/3.x/ru/extras/formit) — документация FormIt
- [FetchIt](https://docs.modx.pro/components/fetchit/) — документация FetchIt
- [API-документация](api.md) — полная спецификация сниппетов и параметров
