<?php
/**
 * Класс базовых функций
 * @author Yuri Frantsevich (FYN)
 * Date: 17/08/2021
 * @version 1.0.1
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
     * @param $data - массис
     * @return object
     */
    public function ArrayToObj ($data) {
        if (!is_array($data) && !is_object($data)) return $data;
        foreach ($data as $key=>$value) {
            if (is_array($value)) $data[$key] = (object) $value;
            if (isset($data->$key) && is_object($data->$key)) $data->$key = $this->ArrayToObj($data->$key);
        }
        $data = (object) $data;
        return $data;
    }

    /**
     * Формируем из объекта массив (только для унификации и удобства)
     * @param $data - объект
     * @return array
     */
    public function ObjToArray ($data) {
        if (!is_array($data) && !is_object($data)) return $data;
        if (is_object($data)) $data = (array) $data;
        foreach ($data as $key=>$value) {
            if (is_object($value)) $data[$key] = (array) $value;
            if (isset($data[$key]) && is_array($data[$key])) $data[$key] = $this->ObjToArray($data[$key]);
        }
        $data = (array) $data;
        return $data;
    }

    /**
     * Вычисляем хэш строки
     * @param $key - строка
     * @param string $alg - ключ используемой функции, по умолчанию md5
     * @return bool|string
     */
    public function getKeyHash ($key, $alg = '') {

        if (!$alg && defined("CRYPT_TYPE")) $alg = CRYPT_TYPE;
        elseif (!$alg) $alg = 'md5';

        switch ($alg) {
            case 'password':
                $key = password_hash($key, PASSWORD_DEFAULT);
                break;
            case 'password_bcrypt':
                $key = password_hash($key, PASSWORD_BCRYPT);
                break;
            case 'crypt':
                $key = crypt($key);
                break;
            case 'crypt_site':
                if (defined("CRYPT_KEY")) $key = crypt($key, CRYPT_KEY);
                else $key = crypt($key);
                break;
            case 'sha1':
                $key = sha1($key);
                break;
            case 'hash':
                $key = hash('sha256', $key);
                break;
            case 'md5':
            default:
                $key = md5($key);
                break;
        }
        return $key;
    }

    /**
     * Определение IP адреса с которого открывается страница
     * @return mixed
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
     * @param $password - проверяемая строка
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
     * @param $date - проверяемая дата
     * @param string $format - формат проверяемой даты
     * @return bool
     */
    public static function validateDate($date, $format = 'd/m/Y H:i:s') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }
}