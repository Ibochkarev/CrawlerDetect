# CrawlerDetect — API документация

**Версия:** 1.0.0-pl  
**Базовый namespace:** `crawlerdetect`  
**Ссылки:** [JayBizzle/Crawler-Detect](https://github.com/JayBizzle/Crawler-Detect) | [FormIt](https://docs.modx.com/3.x/ru/extras/formit) | [FetchIt](https://docs.modx.pro/components/fetchit/)

---

## 1. Введение

CrawlerDetect — дополнение MODX Revolution 3.x для определения ботов по User-Agent и защиты форм без CAPTCHA. «API» компонента — сниппеты и системные настройки.

**Требования:** MODX 3.x, PHP 8.2+.

---

## 2. Сниппет isCrawler

Определяет, является ли текущий посетитель ботом (краулером/пауком).

### 2.1. Спецификация

| Поле | Значение |
|------|----------|
| **Имя** | `isCrawler` |
| **Кэширование** | Не использовать (`[[!isCrawler]]`) |
| **Возврат** | `"1"` (бот) или `"0"` (не бот) |

### 2.2. Параметры (Request)

| Параметр | Тип | Обязательный | По умолчанию | Описание |
|----------|-----|--------------|--------------|----------|
| `userAgent` | string | Нет | `null` | User-Agent для проверки. Если пусто — берётся из `$_SERVER['HTTP_USER_AGENT']` и `$_SERVER['HTTP_FROM']`. |
| `placeholderPrefix` | string | Нет | `crawlerdetect.` | Префикс плейсхолдера для имени бота. Итог: `{prefix}matches`. |

### 2.3. Ответ (Response)

**Возвращаемое значение:**
- `"1"` — обнаружен бот
- `"0"` — не бот

**Плейсхолдеры (при обнаружении бота):**
- `{placeholderPrefix}matches` — имя совпадения (например, `Googlebot`)

### 2.4. Обработка ошибок

| Ситуация | Поведение |
|----------|-----------|
| Библиотека не загружена | Логирование ошибки, возврат `"0"` (fail-open) |
| Пустой `userAgent` при явной передаче | Трактуется как `null`, используется HTTP-заголовок |

### 2.5. Примеры

**MODX — базовый вызов:**
```modx
[[!isCrawler]]
```

**MODX — условный вывод виджета (только не-ботам):**
```modx
[[!isCrawler:eq=`0`:then=`[[$chatWidget]]`]]
```

**MODX — с кастомным User-Agent и выводом имени бота:**
```modx
[[!isCrawler? &userAgent=`[[+custom_user_agent]]`]]
Обнаружен бот: [[+crawlerdetect.matches]]
```

**Fenom — условный вывод:**
```fenom
{if $modx->runSnippet('isCrawler', []) == '0'}
    {$modx->getChunk('chatWidget')}
{/if}
```

**Fenom — с переменной:**
```fenom
{set $isBot = $modx->runSnippet('isCrawler', [])}
{if $isBot == '0'}
    {$modx->getChunk('chatWidget')}
{/if}
```

**Fenom — userAgent и имя бота:**
```fenom
{$modx->runSnippet('isCrawler', ['userAgent' => $custom_user_agent])}
{if $modx->getPlaceholder('crawlerdetect.matches')}
    Обнаружен бот: {$modx->getPlaceholder('crawlerdetect.matches')}
{/if}
```

---

## 3. Сниппет crawlerDetectBlock (FormIt preHook)

PreHook для FormIt: при обнаружении бота прерывает обработку формы и выводит сообщение. Данные не сохраняются, письма не отправляются.

### 3.1. Спецификация

| Поле | Значение |
|------|----------|
| **Имя** | `crawlerDetectBlock` |
| **Тип** | FormIt preHook |
| **Подключение** | `&preHooks=\`crawlerDetectBlock\`` |

### 3.2. Параметры

Параметры не требуются. Поведение задаётся системными настройками:
- `crawlerdetect_block_message` — текст при блокировке
- `crawlerdetect_log_blocked` — логировать ли попытки

### 3.3. Поведение

| Условие | Результат |
|---------|-----------|
| Бот обнаружен | `false`, плейсхолдер `fi.validation_error_message`, при `addError` — ошибка в FormIt |
| Не бот | `true`, обработка формы продолжается |
| Ошибка загрузки сервиса | `true` (fail-open) |

### 3.4. Примеры

**MODX — FormIt с защитой:**
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

**Fenom — FormIt с защитой:**
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

---

## 4. Интеграция с FetchIt

FetchIt отправляет формы через Fetch API; серверная обработка — FormIt. Защита включается тем же preHook.

**Шаги:**
1. В конфигурации FetchIt указать обработчик FormIt.
2. В вызов FormIt добавить `&preHooks="crawlerDetectBlock"`.
3. При блокировке ботом FetchIt получит ответ с ошибкой валидации и покажет `fi.validation_error_message`.

Отдельного кода для FetchIt не требуется.

---

## 5. Интеграция с SendIt

SendIt использует FormIt как обработчик; параметры вызова задаются в пресетах. Защита от ботов включается добавлением preHook в пресет.

**Шаги:**
1. Укажите путь к своему файлу пресетов в системной настройке SendIt **si_path_to_presets** (не редактируйте стандартный файл в `core/components/sendit/presets/` — при обновлении компонента он перезаписывается).
2. В нужный пресет добавьте `'preHooks' => 'crawlerDetectBlock'`.
3. При блокировке ботом SendIt вернёт ответ с ошибкой валидации; сообщение берётся из настройки CrawlerDetect `crawlerdetect_block_message`.

Отдельного кода для SendIt не требуется.

---

## 6. Системные настройки (namespace: crawlerdetect)

| Ключ | Тип | По умолчанию | Описание |
|------|-----|--------------|----------|
| `crawlerdetect_block_message` | text | «Не удалось отправить форму. Попробуйте позже.» | Текст при блокировке формы ботом |
| `crawlerdetect_log_blocked` | boolean | `true` | Логировать заблокированные отправки в лог MODX |

---

## 7. Программный API (PHP)

Сервис доступен через DI:

```php
$service = $modx->services->get('CrawlerDetect\\CrawlerDetectService');
$isBot = $service->isCrawler(null);  // null = из запроса
$matches = $service->getMatches();   // имя бота или пустая строка
```

---

## 8. Счётчик без ботов (US-003)

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

---

## 9. Changelog API

| Версия | Изменения |
|--------|-----------|
| 1.0.0-pl | Первый релиз: isCrawler, crawlerDetectBlock, интеграция FormIt/FetchIt |
