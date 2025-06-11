///////////////////////////////////
///       ocStore V3.x         ///
///  Інструкція зі встановлення ///
///  https://ocstore.com        ///
///////////////////////////////////


ПРИМІТКА: ЦЕ ЛИШЕ ДЛЯ НОВОГО ВСТАНОВЛЕННЯ!
ЯКЩО ОНОВЛЕННЯ В ІСНУЮЧИЙ МАГАЗИН, НЕ ЗАБУДЬТЕ ПРОЧИТАТИ UPGRADE.TXT


-------
Встановлення
-------
These instructions are for a manual installation using FTP, cPanel or other web hosting Control Panel.

- Встановлення на Linux -

1. Завантажте всі ваші файли та папки на ваш сервер з папки «Upload». Можна розархівувати в будь-яке місце на Ваш вибір.
    Наприклад: У cPanel має бути в папці public_html/ і Plesk має бути в httpdocs/.

2. Перейменуйте config-dist.php на config.php і admin/config-dist.php на admin/config.php

3. Для Linux/Unix переконайтеся, що папки та файли доступні для запису.

		chmod 0755 or 0777 system/storage/cache/
		chmod 0755 or 0777 system/storage/download/
		chmod 0755 or 0777 system/storage/logs/
		chmod 0755 or 0777 system/storage/modification/
		chmod 0755 or 0777 system/storage/session/
		chmod 0755 or 0777 system/storage/upload/
		chmod 0755 or 0777 system/storage/vendor/
		chmod 0755 or 0777 image/
		chmod 0755 or 0777 image/cache/
		chmod 0755 or 0777 image/catalog/
		chmod 0755 or 0777 config.php
		chmod 0755 or 0777 admin/config.php

		Якщо при правах 0755 не працює, спробуйте 0777.

4. Упевніться, що у вас встановлено базу даних MySQL і ви маєте доступ до неї. НІ ЗА ЯКИХ ОБСТАВ НЕ ВИКОРИСТОВУЙТЕ ROOT ЛОГІН ТА ПАРОЛЬ.

5. Завітайте на домашню сторінку свого магазину.
Наприклад: http://www.example.com або http://www.examle.com/store/

6. Дотримуйтесь інструкцій на екрані.

7. Видаліть папку для встановлення.

8. Якщо ви завантажили скомпільовану версію з папкою Vendor, то вона повинна бути завантажена вище кореневої директорії (у тій же папці, де public_html або httpdocs)

Встановлення на Windows -

1. Завантажте всі файли та папки на свій сервер із папки «Upload». Їх можна розпакувати у будь-яке місце на ваш вибір. Наприклад /wwwroot/store або /wwwroot

2. Перейменуйте config-dist.php на config.php і admin/config-dist.php на admin/config.php

3. Для Windows переконайтеся, що папки та файли доступні для запису.

		system/storage/cache/
		system/storage/download/
		system/storage/logs/
		system/storage/modification/
		system/storage/session/
		system/storage/upload/
		system/storage/vendor/
		image/
		image/cache/
		image/catalog/
		config.php
		admin/config.php

4. Переконайтеся, що у Вас встановлена база даних MySQL і Ви маєте доступ до неї. НІ В ЯКОМУ РАЗІ НЕ ВИКОРИСТОВУЙТЕ ROOT ЛОГІН І ПАРОЛЬ.

5. Завітайте на домашню сторінку Вашого магазину.
    Наприклад: http://www.example.com або http://www.examle.com/store/

6. Виконуйте вказівки на екрані.

7. Видаліть інсталяційну директорію.

----------------------------
COMPOSER OR NOT TO COMPOSER
----------------------------
From version 2.2 composer has been added to aid developers who want to use composer libraries. 2 versions of OpenCart
will become available, one compiled and one non-compiled (composer.json only - no files in vendor folder).

We STRONGLY advise leaving the vendor folder outside of the webroot - so files cannot be accessed directly.

Composer installing is extremely simple - https://getcomposer.org
