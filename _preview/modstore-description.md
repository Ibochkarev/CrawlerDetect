# CrawlerDetect — защита форм от спама без CAPTCHA

Определение веб-краулеров (ботов) по User-Agent и блокировка отправки форм ботами. Один preHook для FormIt — и контактные формы, заявки, подписки защищены от спама без CAPTCHA.

---

## Возможности

- **Определение ботов** — по User-Agent (библиотека JayBizzle/Crawler-Detect).
- **Защита форм** — preHook `crawlerDetectBlock` для FormIt; работает с FetchIt.
- **Скрытие виджетов** — сниппет `isCrawler` для вывода контента только людям (чат, аналитика, счётчики).
- **Настройки** — текст сообщения при блокировке, логирование.

---

## Быстрый старт

Добавьте `crawlerDetectBlock` в preHooks FormIt:

```
[[!FormIt? &preHooks=`crawlerDetectBlock` &hooks=`email,redirect` ...]]
```

Форма защищена. Сообщение при блокировке настраивается в системных настройках.

---

## Требования

- MODX Revolution 3.x
- PHP 8.2+
- FormIt (для защиты форм)

---

## Документация

- [Руководство пользователя](https://github.com/.../user-guide.md)
- [API](https://github.com/.../api.md)
