# Mail Template Editor for OpenCart 3 / LiveStore

[![Version](https://img.shields.io/badge/version-1.4.0-blue.svg)](https://github.com/damatveev/mte/releases)
[![OpenCart](https://img.shields.io/badge/OpenCart-3.x-blue.svg)](https://www.opencart.com/)
[![PHP](https://img.shields.io/badge/PHP-7.2%2B-777bb4.svg)](https://www.php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

**Mail Template Editor** — бесплатный модуль для редактирования стандартных email-шаблонов OpenCart 3.x и LiveStore непосредственно из административной панели. Поддерживает HTML и plain-text письма, Twig, Summernote, защиту обязательных переменных и автоматическое резервное копирование.

English: Mail Template Editor is an OpenCart 3.x / LiveStore extension for editing standard transactional email templates from the admin panel, with HTML/plain-text detection, Summernote, Twig/printf placeholder protection and automatic backups.

**Версия:** 1.4.0  
**Автор:** Dmitry Matveev  
**E-mail:** d.a.matveev@gmail.com

## Возможности

- редактирование `catalog/language/*/mail/*.php`;
- редактирование `catalog/view/theme/*/template/mail/*.twig`;
- автоматическое определение HTML/plain-text по `setHtml()` / `setText()`;
- Summernote для HTML-писем, если другой редактор не активирован;
- безопасный plain-text режим;
- защита обязательных `printf`-переменных (`%s`, `%d` и др.);
- защита Twig-плейсхолдеров `{{ ... }}`;
- автоматическая резервная копия перед сохранением;
- поддержка нескольких языков и тем OpenCart/LiveStore;
- встроенная ссылка Donate и QR-код.

## Зачем нужен модуль

Стандартные письма магазина обычно приходится менять непосредственно в PHP/Twig-файлах. Mail Template Editor переносит эту работу в административную панель и дополнительно контролирует переменные шаблонов, снижение риска случайно повредить письмо при редактировании.

Подходит для писем о заказах и изменениях статуса, регистрации, восстановлении пароля, партнёрской программе, транзакциях, ваучерах и других стандартных почтовых шаблонов OpenCart.

## Установка

Готовый пакет находится в `dist/mail_template_editor_v1.4.0.ocmod.zip`.

1. Откройте `Дополнения → Установка дополнений`.
2. Загрузите `.ocmod.zip`.
3. Откройте `Дополнения → Модификаторы` и обновите модификаторы.
4. Перейдите в `Дополнения → Дополнения → Модули`.
5. Установите **Редактор шаблонов писем / Mail Template Editor**.
6. Перед использованием убедитесь, что веб-сервер имеет права записи в редактируемые файлы.

## Совместимость

- OpenCart 3.x;
- LiveStore 3.x;
- OCMOD package;
- PHP 7.2+ (фактическую совместимость рекомендуется проверять на конкретной сборке);
- VQMod не требуется.

Сторонние темы и расширения могут добавлять собственные почтовые шаблоны или изменять стандартный механизм отправки, поэтому такие шаблоны следует проверять отдельно.

## Безопасность редактирования

Перед сохранением создаётся резервная копия. Модуль также проверяет наличие обязательных `printf`- и Twig-плейсхолдеров исходного шаблона и не позволяет сохранить вариант, в котором необходимые переменные были удалены.

## Скачать

Актуальные версии публикуются в [GitHub Releases](https://github.com/damatveev/mte/releases). Готовый пакет текущей версии также находится в каталоге [`dist`](https://github.com/damatveev/mte/tree/main/dist).

## Keywords

OpenCart, OpenCart 3, LiveStore, ocStore, email template, mail template, email editor, mail editor, template editor, transactional email, Summernote, Twig, OCMOD, PHP, ecommerce, OpenCart extension, OpenCart module, шаблоны писем, редактор писем.

## Поддержка проекта / Donate

Если модуль оказался полезен, разработку можно поддержать:

https://boosty.to/matveevd/donate

QR-код включён в модуль и отображается в его интерфейсе.

## Автор

**Dmitry Matveev**  
E-mail: d.a.matveev@gmail.com

## Лицензия

Распространяется по лицензии [MIT](LICENSE).