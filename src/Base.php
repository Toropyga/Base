<?php
/**
 * Класс базовых функций
 * @author Yuri Frantsevich (FYN)
 * Date: 17/08/2021
 * @version 1.2.3
 * @copyright 2021
 */

namespace FYN;

use DateTime;

class Base {

    /**
     * Base constructor.
     */
    public function __construct() {
    }

    /**
     * Формируем из массива объект (для унификации и удобства)
     * @param array $data - массив
     * @return object
     */
    public static function ArrayToObj ($data) {
        if (!is_array($data) && !is_object($data)) return $data;
        foreach ($data as $key=>$value) {
            if (is_array($value)) $data[$key] = (object) $value;
            if (isset($data->$key) && is_object($data->$key)) $data->$key = self::ArrayToObj($data->$key);
        }
        $data = (object) $data;
        return $data;
    }

    /**
     * Формируем из объекта массив (только для унификации и удобства)
     * @param object $data - объект
     * @return array
     */
    public static function ObjToArray ($data) {
        if (!is_array($data) && !is_object($data)) return $data;
        if (is_object($data)) $data = (array) $data;
        foreach ($data as $key=>$value) {
            if (is_object($value)) $data[$key] = (array) $value;
            if (isset($data[$key]) && is_array($data[$key])) $data[$key] = self::ObjToArray($data[$key]);
        }
        $data = (array) $data;
        return $data;
    }

    /**
     * Вычисляем хэш строки
     * @param string $string - строка которая шифруется
     * @param string $alg - алгоритм шифрования (тип используемой функции), по умолчанию md5
     * @param string $key - ключ шифрования для алгоритма 'crypt_site'
     * @return bool|string
     */
    public static function getKeyHash ($string, $alg = '', $key = '') {

        if (!$alg && defined("CRYPT_TYPE")) $alg = CRYPT_TYPE;
        elseif (!$alg) $alg = 'md5';

        switch ($alg) {
            case 'password':
                $string = password_hash($string, PASSWORD_DEFAULT);
                break;
            case 'password_bcrypt':
                $string = password_hash($string, PASSWORD_BCRYPT);
                break;
            case 'crypt':
                $string = crypt($string);
                break;
            case 'crypt_site':
                if ($key) $string = crypt($string, $key);
                elseif (defined("CRYPT_KEY")) $string = crypt($string, CRYPT_KEY);
                else $string = crypt($string);
                break;
            case 'sha1':
                $string = sha1($string);
                break;
            case 'hash':
                $string = hash('sha256', $string);
                break;
            case 'md5':
            default:
                $string = md5($string);
                break;
        }
        return $string;
    }

    /**
     * Определение IP адреса с которого открывается страница
     * @return array
     */
    public static function getIP () {
        $ipn = (isset($_SERVER['REMOTE_ADDR']))?$_SERVER['REMOTE_ADDR']:'';
        if (!$ipn) $ipn = urldecode(getenv('HTTP_CLIENT_IP'));
        if (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")) $strIP = getenv('HTTP_X_FORWARDED_FOR');
        elseif (getenv('HTTP_X_FORWARDED') && strcasecmp(getenv("HTTP_X_FORWARDED"), "unknown")) $strIP = getenv('HTTP_X_FORWARDED');
        elseif (getenv('HTTP_FORWARDED_FOR') && strcasecmp(getenv("HTTP_FORWARDED_FOR"), "unknown")) $strIP = getenv('HTTP_FORWARDED_FOR');
        elseif (getenv('HTTP_FORWARDED') && strcasecmp(getenv("HTTP_FORWARDED"), "unknown")) $strIP = getenv('HTTP_FORWARDED');
        else $strIP = (isset($_SERVER['REMOTE_ADDR']))?$_SERVER['REMOTE_ADDR']:'127.0.0.1';
        if ($ipn == '::1') $ipn = '127.0.0.1';
        if ($strIP == '::1') $strIP = '127.0.0.1';
        if (!preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/", $ipn)) $ipn = '';
        if (!preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/", $strIP)) $strIP = $ipn;
        if ($strIP != $ipn) {
            $ip['proxy'] = $ipn;
            $ip['ip'] = $strIP;
        }
        else {
            $ip['proxy'] = '';
            $ip['ip'] = $ipn;
        }
        return $ip;
    }

    /**
     * Отладочная функция для вывода на экран
     * данных содержащихся в переданной переменной $array
     *
     * @param mixed $array
     * @param bool $print - вывод данных на экран
     * @return string
     */
    public static function dump ($array = array(), $print = true) {
        $dump = print_r($array, true);
        $line = date('d.m.Y H:i:s')."<br>Return values:<br>---------------------<pre>".$dump."</pre>---------------------<br>";
        if ($print) echo $line;
        return $dump;
    }

    /**
     * Проверяем пароль на соответствие условиям безопасности
     * @param string $password - проверяемая строка
     * @param int $len - строка имеет длину не менее указанного количества символов
     * @param int $type - тип строки
     *          0 => строка содержит хотя бы одну цифру, хотя бы один спецсимвол, хотя бы одну латинскую букву в нижнем регистре, хотя бы одну латинскую букву в верхнем регистре;
     *          1 => строка содержит хотя бы одну цифру, хотя бы одну латинскую букву в нижнем регистре, хотя бы одну латинскую букву в верхнем регистре;
     *          2 => строка содержит хотя бы одну цифру, хотя бы один спецсимвол, хотя бы одну латинскую букву;
     *          3 => строка содержит хотя бы одну цифру, хотя бы одну латинскую букву;
     *          4 => строка содержит хотя бы одну латинскую букву;
     *          5 => строка содержит хотя бы одну цифру;
     * @return bool
     */
    public static function checkPassword ($password, $len = 6, $type = 0) {
        // Предварительная настройка
        $num = '0-9';                   // числа;
        $sym = '!&@#$%\\^&*_\\+\\-';    // спецсимволы;
        $slt = 'a-z';                   // латинские буквы в нижнем регистре;
        $blt = 'A-Z';                   // латинские буквы в верхнем регистре;
        $alt = 'A-z';                   // латинские буквы

        $template = array(
            0 => array($num, $sym, $slt, $blt), // строка содержит хотя бы одно число, хотя бы один спецсимвол, хотя бы одну латинскую букву в нижнем регистре, хотя бы одну латинскую букву в верхнем регистре;
            1 => array($num, $slt, $blt),       // строка содержит хотя бы одно число, хотя бы одну латинскую букву в нижнем регистре, хотя бы одну латинскую букву в верхнем регистре;
            2 => array($num, $sym, $alt),       // строка содержит хотя бы одно число, хотя бы один спецсимвол, хотя бы одну латинскую букву;
            3 => array($num, $alt),             // строка содержит хотя бы одно число, хотя бы одну латинскую букву;
            4 => array($alt),                   // строка содержит хотя бы одну латинскую букву;
            5 => array($num),                   // строка содержит хотя бы одну цифру;
        );
        $search = '';
        $line = '';
        foreach ($template[$type] as $row) {
            $search .= '(?=.*['.$row.'])';
            $line .= $row;
        }
        $line = '['.$line.']{'.$len.',}';
        $search .= $line;
        if (preg_match("/^$search$/", $password)) return true;
        else return false;
    }

    /**
     * Проверка даты на валидность
     * @param string $date - проверяемая дата
     * @param string $format - формат проверяемой даты
     * @return bool
     */
    public static function validateDate($date, $format = 'd/m/Y H:i:s') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    /**
     * Конвертирование текста в заданную кодировку
     * @param string $line - строка с текстом
     * @param string $enc - заданная кодировка, utf-8 по умолчанию
     * @return string
     */
    public static function convertLine ($line, $enc = 'utf-8') {
        if (!$line) return $line;
        $cod = '';

        // Unicode BOM is U+FEFF, but after encoded, it will look like this.
        $UTF32_BIG_ENDIAN_BOM       = chr(0x00) . chr(0x00) . chr(0xFE) . chr(0xFF);
        $UTF32_LITTLE_ENDIAN_BOM    = chr(0xFF) . chr(0xFE) . chr(0x00) . chr(0x00);
        $UTF16_BIG_ENDIAN_BOM       = chr(0xFE) . chr(0xFF);
        $UTF16_LITTLE_ENDIAN_BOM    = chr(0xFF) . chr(0xFE);
        $UTF8_BOM                   = chr(0xEF) . chr(0xBB) . chr(0xBF);

        $first1 = substr($line, 0, 2);
        $first2 = substr($line, 0, 3);
        $first3 = substr($line, 0, 3);

        if ($first2 == $UTF8_BOM)                       $cod = 'utf-8';
        elseif ($first3 == $UTF32_BIG_ENDIAN_BOM)       $cod = 'utf-32be';
        elseif ($first3 == $UTF32_LITTLE_ENDIAN_BOM)    $cod = 'utf-32le';
        elseif ($first1 == $UTF16_BIG_ENDIAN_BOM)       $cod = 'utf-16be';
        elseif ($first1 == $UTF16_LITTLE_ENDIAN_BOM)    $cod = 'utf-16le';
        if (!$cod) $cod = self::detect_encoding($line);
        if ($cod != $enc) $line = @mb_convert_encoding($line, $enc, $cod);
        return $line;
    }

    /**
     * Определение кодировки текста
     * Используем в функции convertLine
     * @param $string - строка с текстом
     * @param int $pattern_size - максимальная длина строки для парсинга
     * @return mixed|string
     */
    public static function detect_encoding ($string, $pattern_size = 50) {
        $list = array(
            'utf-8', 'ascii', 'cp1251', 'KOI8-R', 'CP866', 'KOI8-U', 'HTML-ENTITIES',
            'ISO-8859-1', 'ISO-8859-2', 'ISO-8859-3', 'ISO-8859-4', 'ISO-8859-5', 'ISO-8859-6', 'ISO-8859-7',
            'ISO-8859-8', 'ISO-8859-9', 'ISO-8859-10', 'ISO-8859-13', 'ISO-8859-14', 'ISO-8859-15', 'ISO-8859-16',
            'Windows-1251', 'Windows-1252', 'Windows-1254', 'UCS-2LE', 'UTF-7',
            'utf-32be', 'utf-32le', 'utf-16be', 'utf-16le', 'JIS', 'SJIS', 'eucjp-win', 'sjis-win', 'gbk');
        $enc = '';

        // Unicode BOM is U+FEFF, but after encoded, it will look like this.
        $UTF32_BIG_ENDIAN_BOM       = chr(0x00) . chr(0x00) . chr(0xFE) . chr(0xFF);
        $UTF32_LITTLE_ENDIAN_BOM    = chr(0xFF) . chr(0xFE) . chr(0x00) . chr(0x00);
        $UTF16_BIG_ENDIAN_BOM       = chr(0xFE) . chr(0xFF);
        $UTF16_LITTLE_ENDIAN_BOM    = chr(0xFF) . chr(0xFE);
        $UTF8_BOM                   = chr(0xEF) . chr(0xBB) . chr(0xBF);

        $first1 = substr($string, 0, 2);
        $first2 = substr($string, 0, 3);
        $first3 = substr($string, 0, 3);

        if ($first2     == $UTF8_BOM)                   $enc = 'utf-8';
        elseif ($first3 == $UTF32_BIG_ENDIAN_BOM)       $enc = 'utf-32be';
        elseif ($first3 == $UTF32_LITTLE_ENDIAN_BOM)    $enc = 'utf-32le';
        elseif ($first1 == $UTF16_BIG_ENDIAN_BOM)       $enc = 'utf-16be';
        elseif ($first1 == $UTF16_LITTLE_ENDIAN_BOM)    $enc = 'utf-16le';

        if (!$enc) {
            $c = strlen($string);
            if ($c > $pattern_size) {
                $string = substr($string, floor(($c - $pattern_size) / 2), $pattern_size);
                $c = $pattern_size;
            }

            $reg1 = '/(\xE0|\xE5|\xE8|\xEE|\xF3|\xFB|\xFD|\xFE|\xFF)/i';
            $reg2 = '/(\xE1|\xE2|\xE3|\xE4|\xE6|\xE7|\xE9|\xEA|\xEB|\xEC|\xED|\xEF|\xF0|\xF1|\xF2|\xF4|\xF5|\xF6|\xF7|\xF8|\xF9|\xFA|\xFC)/i';

            $mk = 10000;
            $enc = 'utf-8';
            foreach ($list as $item) {
                $sample1 = @iconv($item, 'cp1251', $string);
                $gl = @preg_match_all($reg1, $sample1, $arr);
                $sl = @preg_match_all($reg2, $sample1, $arr);
                if (!$gl || !$sl) continue;
                $k = abs(3 - ($sl / $gl));
                $k += $c - $gl - $sl;
                if ($k < $mk) {
                    $enc = $item;
                    $mk = $k;
                }
            }
        }

        if (!$enc && function_exists("mb_detect_encoding")) $enc = @mb_detect_encoding($string, $list, true);

        return $enc;
    }

    /**
     * Экранирование данных
     * защищаемся от передачи вредоносных запросов
     * @param array|string $array - строка или массив данных
     * @param string $code - кодировка текста
     * @return array|string
     */
    public static function screeningData ($array, $code = 'utf-8') {
        if (!is_array($array)) {
            $array = strtr($array, array("&"=>"&amp;"));
            return htmlentities($array, ENT_QUOTES);
        }
        foreach ($array as $key => $row) {
            $key = htmlentities($key, ENT_QUOTES);
            if (is_array($row)) $array[$key] = self::screeningData($row);
            else {
                $row = self::convertLine($row, $code);
                $row = strtr($row, array("&"=>"&amp;"));
                $array[$key] = htmlentities($row, ENT_QUOTES);
            }
        }
        return $array;
    }

    /**
     * Деэкранирование данных
     * @param array|string $array
     * @return array|string
     */
    public static function unscreeningData ($array) {
        if (!is_array($array)){
            $array = html_entity_decode($array);
            return strtr($array, array("&amp;"=>"&"));
        }
        foreach ($array as $key => $row) {
            $key = html_entity_decode($key);
            if (is_array($row)) $array[$key] = self::unscreeningData($row);
            else {
                $row = html_entity_decode($row);
                $array[$key] = strtr($row, array("&amp;"=>"&"));
            }
        }
        return $array;
    }

    /**
     * Установка заголовков
     * @param string $type      - тип контента ('text/html', 'application/json', 'text/xml')
     * @param string $CODE      - кодировка текста ('utf-8', 'ascii', 'cp1251', 'KOI8-R', 'CP866', 'KOI8-U', 'ISO-8859-1' и т.д.)
     * @param array $methods    - допустимые методы взаимодействия ('GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'HEAD', 'CONNECT', 'TRACE', 'PATCH')
     * @param string $servers   - сервера с которыми допускается взаимодействие ('*' - любой, <origin> - один источник [https://google.com] , null - нежелательный параметр)
     * @param string $protocol  - протокол передачи данных (http, https, ftp)
     * @param integer $lifetime - "время жизни" страницы при безопасном соединении (https) в секундах
     * @return bool
     */
    public static function setHeaders ($type = 'text/html', $CODE = 'utf-8', $methods = array('GET', 'POST', 'OPTIONS'), $servers = '*', $protocol = 'https', $lifetime = 2400) {
        $types = array('text/html', 'application/json', 'text/xml');
        if (!in_array($type, $types)) $type = $types[0];
        $server_methods = '';
        foreach ($methods as $method) {
            if (in_array(mb_strtoupper($method), array('GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'HEAD', 'CONNECT', 'TRACE', 'PATCH'))) $server_methods .= ($server_methods)?", ".mb_strtoupper($method):mb_strtoupper($method);
        }
        if (!$server_methods) $server_methods = "GET";
        if ($servers !== "*" || $servers !== null || $servers !== "null" || !preg_match("/^http(s)?:\/\/[^\s]+/", $servers)) $servers = "*";
        if ($servers === null) $servers = "null";
        if (!in_array($protocol, array('http', 'https', 'ftp'))) $protocol = 'https';
        if ($lifetime <= 5) $lifetime = 2400;
        header("Access-Control-Allow-Origin: ".$servers);
        header("Access-Control-Allow-Methods: ".$server_methods);
        header("Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token");
        header("X-XSS-Protection: 1; mode=block");
        header("X-Content-Type-Options: nosniff");
        if ($protocol == 'https') {
            header("Strict-Transport-Security: max-age=$lifetime; preload; includeSubDomains");
            header("Expect-CT: max-age=$lifetime, enforce");
        }
        header("X-Frame-Options: DENY");
        header("Content-Security-Policy: frame-ancestors 'self'");
        header("Content-Type: $type; charset=$CODE");
        return true;
    }

    /**
     * Расчёт параметров пагинации
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
     */
    public static function getPagination ($sum, $page = 1, $res_on_page = 20, $pages_show = 5) {
        if (!$page) $page = 1;
        settype($res_on_page, "integer");
        settype($pages_show, "integer");
        if (!$res_on_page || $res_on_page < 5) $res_on_page = 20;
        if (!$pages_show || $pages_show < 3) $pages_show = 5;
        $pages = ceil($sum/$res_on_page);
        if ($page == $pages) $now_view = $sum - ($res_on_page*($page - 1));
        else $now_view = $res_on_page;
        if (($pages - $pages_show) <= 0) $pages_show = $pages;
        $koe = ceil($pages_show/2);
        if ($page <= $koe) $begin = 1;
        elseif(($page+$koe-1) >= $pages) $begin = (($pages-$pages_show) >= 0)?$pages-$pages_show+1:1;
        else $begin = $page - $koe + 1;
        $end = $begin + $pages_show - 1;
        $forward = ($begin >= 2)?true:false;
        $back = (($pages - $end) > 1)?true:false;

        $pagination = array();
        $pagination['sum'] = $sum;              // общее количество записей
        $pagination['pages'] = $pages;          // общее количество страниц
        $pagination['page'] = $page;            // номер отображаемой страницы
        $pagination['size'] = $res_on_page;     // количество записей на страницах
        $pagination['size_now'] = $now_view;    // количество записей на текущей странице
        $pagination['begin'] = $begin;          // страница с которой начинается текущий отсчёт
        $pagination['end'] = $end;              // страница которой заканчивается текущий отсчёт
        $pagination['forward'] = $forward;      // показывать ли быстрый переход в начало
        $pagination['back'] = $back;            // показывать ли быстрый переход в конец
        return $pagination;
    }

    /**
     * Формирование из массива XML файла
     * @param array $array - переданный массив
     * @param null $title - имя первичного тэга, по умолчанию 'root'
     * @param bool $first - первое вхождение, поддерживаем внутреннюю цикличность, если обрабатываем массив массивов, то при последующих обращениях не прописываем заголовки
     * @return string
     */
    public static function xml_encode ($array, $title = null, $first = true) {
        if ($first) {
            $result = '<?xml version="1.0"?>'."\n";
            if ($title) $result .= "<$title>\n";
            else $result .= "<root>\n";
        }
        else $result = '';
        foreach ($array as $key => $value) {
            if (is_numeric($key)) $key = 'item_' . $key;
            if (is_array($value)) {
                $result .= "<$key>\n";
                $result .= self::xml_encode($value, null, false);
                $result .= "</$key>\n";
            }
            else {
                $value = strtr($value, array("<" => "&lt;", ">" => "&gt;", "'" => "&apos;", '"' => "&quot;", "&"=>"&amp;"));
                $result .= "<$key>$value</$key>\n";
            }
        }
        if ($first) {
            if ($title) $result .= "</$title>\n";
            else $result .= "</root>\n";
        }
        return $result;
    }

    /**
     * Получение Json или массива из XML
     * @param string $xml - данные в формате XML
     * @param boolean $array - вернуть как массив (true) или вернуть как Json (false)
     * @return mixed
     */
    public static function xml_decode ($xml, $array = true) {
        if (!($line = simplexml_load_string($xml))) return false;
        $json = json_encode($line);
        if ($array) $return = json_decode($json,true);
        else $return = $json;
        return $return;
    }
}