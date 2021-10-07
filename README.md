# <div align="center">Система комментирования "CommentZone"</div>

`PHP 5.6+` `Bootstrap 5` `Vue.js 2` `MySQL`

Серверная часть CommentZone разработана на PHP, пользовательская на Vue.js и Bootstrap. Данные хранятся в базе MySQL. Чтобы установить систему комментариев, нужно скачать архив и загрузить файлы на сервер. Размещать файлы можно где угодно, в корневой директории или в какой-то папке, можно даже вынести за пределы публичной директории. Главное при подключении правильно указать пути.

<hr>

<div>Дополнительная информация / Демо - https://wcoding.ru/commentzone/</div>
<div>Документация - https://wcoding.ru/commentzone/docs/</div>


## Установка

### Шаг 1
Создайте базу данных.

### Шаг 2
Загрузите папку CommentZone из архива в директорию вашего сайта.

### Шаг 3
Для запуска инсталятора, создайте PHP файл в директории вашего сайта и добавьте в него код подключения инсталятора:
```php
<?php
define('COMMENTZONE', true);
require __DIR__ . '/CommentZone/install.php';
```
Запустите созданный файл в браузере и следуйте указаниям. После установки удалите созданный файл.

На этом этапе, в корневой директории сайта, создается папка с ресурсами системы для хранения публичных файлов (стили, изображения и прочее). Так же создаются таблицы в БД и администратор.

### Шаг 4
Встрайваем форму комментариев в шаблон ваших страниц следующим кодом:

**Вариант 1**

PHP код:
```php
<?php
define('COMMENTZONE', true);
$urlComment = '/'; //необязательный параметр - здесь можно указать url, тогда url из адресной строки будет игнорироваться
$bindIdComment = 1; //необязательный параметр - здесь можно указать id страницы, тогда url будет игнорироваться, а выборка коментариев будет по id страницы
require __DIR__ . '/CommentZone/index.php';
?>
```
Кроме этого, на странице должна быть запущена сессия. Если она у вас не запущена, добавьте в начале страницы код запуска: `<?php session_start(); ?>`

Например, полный код страницы может выглядеть так:
```php
<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Страница с комментариями</title>
</head>

<body>
<!-- ... -->
  <div class="comment-block">
    <?php
    define('COMMENTZONE', true);
    $urlComment = '/';
    require __DIR__ . '/CommentZone/index.php';
    ?>
  </div>
<!-- ... -->
</body>

</html>
```
**Вариант 2**

Можно встроить через javascript. Для этого создайте PHP файл в директории вашего сайта и добавьте в него код подключения:
```php
<?php
define('COMMENTZONE', true);
require __DIR__ . '/CommentZone/connect.php';
```
Далее в шаблон ваших страниц добавляем следующий код:
```html
<div id="comment-zone"></div>

<script src="/czResource/js/connect.js"></script>

<script>
  var comment = new CommentZone();
  comment.contain = 'comment-zone';
  comment.url = '/'; //необязательный параметр - здесь можно указать url, тогда url из адресной строки будет игнорироваться
  comment.bindId = 1; //необязательный параметр - здесь можно указать id страницы, тогда url будет игнорироваться, а выборка коментариев будет по id страницы
  comment.connect('/comment.php'); //указываем относительный путь к созданному файлу (/comment.php здесь в качестве примера)
</script>
```
Полный код страницы может выглядеть так:
```html
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Страница с комментариями</title>
</head>

<body>
<!-- ... -->

<div id="comment-zone"></div>

<!-- ... -->

<script src="/czResource/js/connect.js"></script>
<script>
  var comment = new CommentZone();
  comment.contain = 'comment-zone';
  comment.url = '/';
  comment.connect('/comment.php');
</script>

</body>

</html>
```
### Шаг 5
Теперь нужно создать страницу подключения к api. Для этого создайте PHP файл (например api.php) и добавьте туда код подключения:
```php
<?php
define('COMMENTZONE', true);
require __DIR__ . '/CommentZone/api.php';
```
После этого нужно открыть файл конфигурации `/CommentZone/config/common.php` и в параметре `api_path` указать относительный путь к созданному файлу (по умолчанию /api.php)

### Шаг 6
Размещаем панель администратора. Для этого создайте страницу, где будет находится панель и добавьте следующий PHP код:
```php
<?php
define('COMMENTZONE', true);
require __DIR__ . '/CommentZone/panel.php';
```
