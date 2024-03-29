# Base library
Базовые функции PHP "на каждый день".

![License](https://img.shields.io/badge/license-MIT-brightgreen.svg)
![Version](https://img.shields.io/badge/version-v1.5.3-blue.svg)
![PHP](https://img.shields.io/badge/php-v7_--_v8-blueviolet.svg)

## Содержание

- [Общие понятия](#общие-понятия)
- [Установка](#установка)
- [Подключение](#подключение)
- [Функции](#функции)
    - [Конвертация массива в объект](#конвертация-массива-в-объект)
    - [Конвертация объекта в массив](#конвертация-объекта-в-массив)
    - [Определение IP адреса](#определение-ip-адреса)
    - [Отладочная функция](#отладочная-функция)
    - [Проверка пароля](#проверка-пароля)
    - [Проверка даты](#проверка-даты)
    - [Конвертация текста в заданную кодировку](#конвертация-текста-в-заданную-кодировку)
    - [Определение кодировки текста](#определение-кодировки-текста)
    - [Экранирование данных](#экранирование-данных)
    - [Деэкранирование данных](#деэкранирование-данных)
    - [Вычисление хэша строки](#вычисление-хэша-строки)
    - [Установка заголовков HEADERS](#установка-заголовков-headers)
    - [Расчёт параметров пагинации](#расчёт-параметров-пагинации)
    - [Формирование XML из массива](#формирование-xml-из-массива)
    - [Получение Json или массива из XML](#получение-Json-или-массива-из-xml)
    - [Установка Cookie](#установка-cookie)
    - [Обратимое шифрование строки](#обратимое-шифрование-строки)

## Общие понятия

Класс Base - это набор базовых функций "на каждый день".
Для работы необходимо наличие PHP версии 5 и выше.

## Установка

Рекомендуемый способ установки библиотеки NetContent с использованием [Composer](http://getcomposer.org/):

```bash
composer require toropyga/base
```
или просто скачайте и сохраните библиотеку в нужную директорию.

## Подключение
```php
require_once("vendor/autoload.php");
```
после этого прописываем
```php
use FYN\Base;
```

## Функции 

### Конвертация массива в объект 
(для унификации и удобства)
```php
/**
 * @param array $data - массив
 * @return object
 *
 * function ArrayToObj ($data);
 */
```
Пример:
```php
$data = Base::ArrayToObj($data);
```
### Конвертация объекта в массив 
(для унификации и удобства)
```php
/**
 * @param object $data - объект
 * @return array
 *
 * function ObjToArray ($data);
 */
```
Пример:
```php
$data = Base::ObjToArray($data);
```
### Определение IP адреса
```php
/**
 * Возвращает ассоциативный массив с ключами:
 *     'ip'    - текуций IP-адрес
 *     'proxy' - IP-адрес используемого прокси сервера, если возможно определить
 * @return array
 *
 * function getIP ();
 */
```
Пример:
```php
$IP = Base::getIP();
```
### Отладочная функция 
для вывода на экран или возврата отформатированных данных содержащихся в переданной переменной $array
```php
/**
 * @param mixed $array
 * @param bool $print - вывод данных на экран (true/false)
 * @return string
 *
 * function dump ($array = array(), $print = true);
 */
```
Пример:
```php
Base::dump($array, true);
или
$dump = Base::dump($array, false);
```
### Проверка пароля
на соответствие условиям безопасности
```php
/**
 * @param string $password - проверяемая строка
 * @param int $len - строка имеет длину не менее указанного количества символов
 * @param int $type - тип строки
 *     0 => строка содержит хотя бы одну цифру, хотя бы один спецсимвол, хотя бы одну латинскую букву в нижнем регистре, хотя бы одну латинскую букву в верхнем регистре;
 *     1 => строка содержит хотя бы одну цифру, хотя бы одну латинскую букву в нижнем регистре, хотя бы одну латинскую букву в верхнем регистре;
 *     2 => строка содержит хотя бы одну цифру, хотя бы один спецсимвол, хотя бы одну латинскую букву;
 *     3 => строка содержит хотя бы одну цифру, хотя бы одну латинскую букву;
 *     4 => строка содержит хотя бы одну латинскую букву;
 *     5 => строка содержит хотя бы одну цифру;
 * @return bool
 *
 * function checkPassword ($password, $len = 6, $type = 0);
 */
```
Пример:
```php
if (Base::checkPassword ($password, 8, 0)) {
    echo "Valid password";
}
else {
    echo "Password is not valid";
}
```
### Проверка даты
на существование
```php
/**
 * @param string $date - проверяемая дата
 * @param string $format - формат проверяемой даты, по умолчанию 'd/m/Y H:i:s'
 * @return bool
 *
 * function validateDate($date, $format = 'd/m/Y H:i:s');
 */
```
Пример:
```php
$date = '09.08.2020';
$format = 'd.m.Y';
if (Base::validateDate($date, $format)) {
    echo "Valid date";
}
else {
    echo "Wrong date"
}
```
### Конвертация текста в заданную кодировку
```php
/**
 * @param string $line - строка с текстом
 * @param string $enc - заданная кодировка, utf-8 по умолчанию ('utf-8', 'ascii', 'cp1251', 'KOI8-R', 'CP866', 'KOI8-U' и т.д.)
 *
 * function convertLine ($line, $enc = 'utf-8');
 */
```
Пример:
```php
$text = Base::convertLine($line, 'cp1251');
```
### Определение кодировки текста 
Работает даже если не отработала функция mb_detect_encoding.
```php
/**
 * @param string $string - строка с текстом
 * @param int $pattern_size - максимальная длина строки для парсинга
 *
 * function detect_encoding ($string, $pattern_size = 50);
 */
```
Пример:
```php
$code = Base::detect_encoding($string);
```
или
```php
$code = Base::detect_encoding($string, 100);
```
### Экранирование данных
защищаемся от передачи вредоносных запросов
```php
/**
 * @param array|string $array - строка или массив данных
 * @param string $code - кодировка текста, по умолчанию 'utf-8'
 * @return array|string
 *
 * function screeningData ($array, $code = 'utf-8');
 */
```
Пример:
```php
$array = Base::screeningData($array, 'cp1251');
```
### Деэкранирование данных
```php
/**
 * Деэкранирование данных
 * @param array $array
 * @return array|string
 *
 * function unscreeningData($array);
 */
```
Пример:
```php
$array = Base::unscreeningData($array);
```
### Вычисление хэша строки

Предварительно можно задать две глобальные переменные:
* CRYPT_TYPE  - алгоритм шифрования (см. $alg)
* CRYPT_KEY - ключ шифрования (см. $key)
```php
/**
 * @param string $string - строка которая шифруется
 * @param string $alg - алгоритм шифрования (тип используемой функции), по умолчанию md5
 *    'password'         => password_hash($key, PASSWORD_DEFAULT);
 *    'password_bcrypt'  => password_hash($key, PASSWORD_BCRYPT);
 *    'crypt'            => crypt($key);
 *    'crypt_site':      => crypt($key, CRYPT_KEY);
 *    'sha1'             => sha1($key);
 *    'hash'             => hash('sha256', $key);
 *    'md5'              => md5($key);
 * @param string $key - ключ шифрования для алгоритма 'crypt_site' 
 * @return bool|string
 *
 * function getKeyHash ($string, $alg = '', $key = '');
 */
```
Пример:
```php
$hash = Base::getKeyHash($sfring, 'sha1');
```
или
```php
$hash = Base::getKeyHash($sfring, 'crypt_site', 'your_code_word');
```
### Установка заголовков HEADERS
```php
/**
 * @param string $type      - тип контента ('text/html', 'application/json', 'text/xml')
 * @param string $CODE      - кодировка текста ('utf-8', 'ascii', 'cp1251', 'KOI8-R', 'CP866', 'KOI8-U', 'ISO-8859-1' и т.д.)
 * @param array $methods    - допустимые методы взаимодействия ('GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'HEAD', 'CONNECT', 'TRACE', 'PATCH')
 * @param string $servers   - сервера с которыми допускается взаимодействие ('*' - любой, <origin> - один источник [https://google.com] , null - нежелательный параметр)
 * @param string $protocol  - протокол передачи данных (http, https, ftp) 
 * @param integer $lifetime - "время жизни" страницы при безопасном соединении (https) в секундах
 * @return bool
 *
 * function setHeaders ($type = 'text/html', $CODE = 'utf-8', $methods = array('GET', 'POST', 'OPTIONS'), $servers = '*', $protocol = 'https', $lifetime = 2400);
 */
```
Пример:
```php
Base::setHeaders('text/json', 'utf-8', array('GET', 'POST', 'OPTIONS'), '*', 'https', 1200);
```
### Расчёт параметров пагинации
```php
/**
 * @param int $sum - общее количество записей
 * @param int $page - текущая страница
 * @param int $res_on_page - количество записей на странице (LIMIT)
 * @param int $pages_show - количество отображаемых закладок
 * @return array
 *      keys (ключи ответа):
 *          sum         - общее количество записей
 *          pages       - общее количество страниц
 *          page        - номер отображаемой страницы
 *          size        - максимальное количество записей на страницу
 *          size_now    - количество записей на текущей странице
 *          begin       - страница с которой начинается текущий отсчёт
 *          end         - страница которой заканчивается текущий отсчёт
 *          forward     - показывать ли быстрый переход в начало (true/false)
 *          back        - показывать ли быстрый переход в конец (true/false)
 *
 * function getPagination ($sum, $page = 1, $res_on_page = 20, $pages_show = 5);
 */
```
Пример:
```php
$pagination = Base::getPagination(187, 1, 25, 5);
```
### Формирование XML из массива
```php
/**
 * @param array $array - переданный массив
 * @param string|null $title - имя первичного тэга, по умолчанию 'root'
 * @param bool $first - первое вхождение, поддерживаем внутреннюю цикличность, если обрабатываем массив массивов, то при последующих обращениях не прописываем заголовки
 * @return string
 *
 * function xml_encode ($array, $title = null, $first = true);
 */
```
Пример:
```php
$xml = Base::xml_encode ($array, $title, $first);
```
### Получение Json или массива из XML
```php
/**
 * @param string $xml - данные в формате XML
 * @param boolean $array - вернуть как массив (true) или вернуть как Json (false)
 * @return mixed
 *
 * function xml_decode ($xml, $array = true)
 */
```
Пример:
```php
$json = Base::xml_decode($xml, false);
```
### Установка Cookie
```php
/**
 * @param string $text - значение Cookie
 * @param string $name - имя Cookie
 * @param int $live_time - срок действия Cookie в секундах с текущего момента
 * @param string $domain - домен определяет, на каком домене доступен файл Cookie
 * @param boolean $secure - передавать Cookie только по HTTPS-протоколу
 * @param boolean $http_only - запретить любой доступ к Cookie из JavaScript
 * @param string $samesite - установка доступности межсайтовых запросов к Cookie('', 'Strict', 'Lax')
 * @param string $path - URL-префикс пути к Cookie
 *
 * function setCookie ($text, $name, $live_time = 86400, $domain = 'localhost', $secure = true, $http_only = false, $samesite = 'lax', $path = '/')
 */
```
Пример:
```php
Base::setCookie ($text, $name, 3600, $domain = 'localhost', true, true);
```

### Обратимое шифрование строки
Шифрование
```php
/**
 * Шифрование строки
 * Ключ шифрования указывается в настройках
 * @param string $string - шифруемая строка
 *
 * function MEncrypt ($string)
 */
```
Пример:
```php
$encoded_text = Base::MEncrypt ('text');
```
Дешифрование
```php
/**
 * Дешифрование строки, зашифрованной функцией MEncrypt
 * @param string $ciphertext - дешифруемая строка
 *
 * public function MDecrypt ($ciphertext)
 */
```
Пример:
```php
$decoded_text = Base::MEncrypt ($encoded_text);
```