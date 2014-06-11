<?php

class Bx
{
    /**
     * Возвращает значение параметра $name из глобального GET-массива
     *
     * @param string $name
     * @param mixed $defaultValue
     * @return mixed
     */
    public static function getGet($name, $defaultValue = null)
    {
        return self::_getValueByComplexKeyFromArray($name, $_GET, $defaultValue);
    }

    /**
     * Возвращает значение параметра $name из глобального POST-массива
     *
     * @param string $name
     * @param mixed $defaultValue
     * @return mixed
     */
    public static function getPost($name, $defaultValue = null)
    {
        return self::_getValueByComplexKeyFromArray($name, $_POST, $defaultValue);
    }

    /**
     * Возвращает значение cookie с ключом $key
     *
     * @param string $key
     * @param mixed $defaultValue
     * @return mixed
     */
    public static function cookieGet($key, $defaultValue = null)
    {
        if (isset($_COOKIE[$key])) {
            return $_COOKIE[$key];
        }

        return $defaultValue;
    }

    /**
     * Сохраняет значение $value в cookie с ключом $key
     *
     * @param string $key
     * @param mixed $value
     * @param mixed $ttl
     * @return bool
     */
    public static function cookieSet($key, $value = null, $ttl = false)
    {
        $domain = '.' . str_replace('www.', '', getenv('HTTP_HOST'));

        if ($ttl === false) {
            $ttl = 2592000; // 60 * 60 * 60 * 12
        }

        if ($value === null) {
            unset($_COOKIE[$key]);
            return setcookie($key, '', time() - 3600, '/', $domain);
        }

        $_COOKIE[$key] = $value;
        return setcookie($key, $value, time() + $ttl, '/', $domain);
    }

    /**
     * Возвращает переменную сессии
     *
     * @param string $key
     * @param mixed $defaultValue
     * @return mixed
     */
    public static function sessionGet($key, $defaultValue = null)
    {
        return self::_getValueByComplexKeyFromArray($key, $_SESSION, $defaultValue);
    }

    /**
     * Устанавливает значение переменной сессии
     *
     * @param string $key
     * @param mixed $value
     */
    public static function sessionSet($key, $value = null)
    {
        if ($value === null) {
            unset($_SESSION[$key]);
        } else {
            $_SESSION[$key] = $value;
        }
    }

    /**
     * Транслитерация по ГОСТ 7.79-2000
     *
     * @param string $str
     * @return string
     */
    public static function translit($str)
    {
        $str = strtolower($str);
        $str = preg_replace('/[\s]+/ui', '_', trim($str));
        $str = preg_replace('/[^а-я_]+/ui', '', $str);

        $replace = array(
            'а' => 'a',
            'б' => 'b',
            'в' => 'v',
            'г' => 'g',
            'д' => 'd',
            'е' => 'e',
            'ё' => 'yo',
            'ж' => 'zh',
            'з' => 'z',
            'и' => 'i',
            'й' => 'j',
            'к' => 'k',
            'л' => 'l',
            'м' => 'm',
            'н' => 'n',
            'о' => 'o',
            'п' => 'p',
            'р' => 'r',
            'с' => 's',
            'т' => 't',
            'у' => 'u',
            'ф' => 'f',
            'х' => 'x',
            'ц' => 'c',
            'ч' => 'ch',
            'ш' => 'sh',
            'щ' => 'shh',
            'ъ' => '',
            'ы' => 'y',
            'ь' => '',
            'э' => 'e',
            'ю' => 'yu',
            'я' => 'ya'
        );

        return strtr($str, $replace);
    }

    /**
     * Множественное число существительных
     *
     * @param integer $x количество
     * @param string $w1 товар (1)
     * @param string $w2 товара (2)
     * @param string $w5 товаров (5)
     * @return string
     */
    public static function plural($x, $w1, $w2, $w5)
    {
        $w = array($w1, $w2, $w5);
        $d = ($p = $x % 100) % 10;

        return $w[$p == 11 || $d == 0 || ($p >= 10 && $p <= 20) || ($d >= 5 && $d <= 9) ? 2 : ($d == 1 ? 0 : 1)];
    }

    /**
     * Форматирует цисло в цену
     *
     * @param mixed $price
     * @return string
     */
    public static function formatPrice($price)
    {
        return number_format($price, 0, ',', ' ');
    }

    /**
     * Форматирует телефон удаляя из него лишние символы и обрезает до 9 знаков
     *
     * @param mixed $phone
     * @return string
     */
    public static function formatPhone($phone)
    {
        $phone = preg_replace('/([^0-9]+)/', '', $phone);

        if (strlen($phone) > 10) {
            $phone = substr($phone, -10);
        }

        return $phone;
    }

    /**
     * Форматирование телефонного номера
     * по шаблону и маске для замены
     *
     * @param string $phone
     * @param string|array $format
     * @param string $mask
     * @return bool|string
     */
    public static function formatPhoneMask($phone, $format, $mask = '#')
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (is_array($format)) {
            if (array_key_exists(strlen($phone), $format)) {
                $format = $format[strlen($phone)];
            } else {
                return false;
            }
        }

        $pattern = '/' . str_repeat('([0-9])?', substr_count($format, $mask)) . '(.*)/';

        $format = preg_replace_callback(
            str_replace('#', $mask, '/([#])/'),
            function () use (&$counter) {
                return '${' . (++$counter) . '}';
            },
            $format
        );

        return ($phone) ? trim(preg_replace($pattern, $format, $phone, 1)) : false;
    }

    /**
     * Преобразовывает переданные данные в JSON объект и завершает вывод
     *
     * @param mixed $data
     * @param mixed $options
     * @param string $callback
     */
    public static function endJson($data, $options = 0, $callback = '')
    {
        $result = json_encode($data, $options);

        if ($callback) {
            header('Content-Type: application/javascript;');
            echo $callback . '(' . $result . ');';
        } else {
            header('Content-Type: application/json;');
            echo $result;
        }

        exit();
    }

    /**
     * Возвращает хеш ключ для кеша, на основе любого числа входных параметров
     *
     * @return string
     */
    public static function cacheId()
    {
        $cacheId = array_merge(func_get_args());

        return md5(implode('|', $cacheId));
    }

    /**
     * Враппер вокруг стандартного редиректа Битрикса
     *
     * @param string $url
     * @param string $status
     */
    public static function redirect($url, $status = '301 Moved permanently')
    {
        LocalRedirect($url, false, $status);
    }

    /**
     * Добавляет событие в почтовую систему для отправки
     *
     * @param string $type
     * @param integer $template
     * @param array $params
     */
    public static function sendMail($type, $template, $params = array())
    {
        CEvent::Send($type, SITE_ID, $params, 'N', $template);
        CEvent::CheckEvents();
    }

    /**
     * Выполнить произвольный запрос к базе данных и вернуть объект CDBResult
     *
     * @param string $sql
     * @param array $params
     * @return CDBResult
     */
    public static function query($sql, $params = array())
    {
        global $DB;

        $sql = self::_prepareQuery($sql, $params);

        return $DB->Query($sql);
    }

    /**
     * Выполнить INSERT запрос к базе данных и вернуть ID вставленной записи или false в случае ошибки.
     *
     * @param string $table
     * @param array $fields
     * @param array $params
     * @return mixed
     */
    public static function insert($table, $fields = array(), $params = array())
    {
        global $DB;

        $fields = self::_prepareQuery($fields, $params);

        $DB->StartTransaction();
        $id = $DB->Insert($table, $fields, __LINE__);

        if ($id !== false) {
            $DB->Commit();

            return $id;
        }

        $DB->Rollback();

        return false;
    }

    /**
     * Выполнить UPDATE запрос к базе данных и вернуть ID вставленной записи или false в случае ошибки.
     *
     * @param string $table
     * @param array $fields
     * @param array $params
     * @param string $where
     * @return mixed
     */
    public static function update($table, $fields = array(), $params = array(), $where = '')
    {
        global $DB;

        $fields = self::_prepareQuery($fields, $params);

        if (!$where) {
            return false;
        }

        $DB->StartTransaction();
        $DB->Update($table, $fields, 'WHERE ' . $where);
        $DB->Commit();

        return true;
    }

    /**
     * Выполнить SELECT запрос к базе данных и вернуть первый рузельтат
     *
     * @param string $sql
     * @param array $params
     * @return array
     */
    public static function find($sql, $params = array())
    {
        $dataProvider = self::findAll($sql, $params);

        return $dataProvider ? array_shift($dataProvider) : false;
    }

    /**
     * Выполнить SELECT запрос к базе данных и вернуть массив результатов
     *
     * @param string $sql
     * @param array $params
     * @return array
     */
    public static function findAll($sql, $params = array())
    {
        global $DB;

        $dataProvider = array();

        $sql = self::_prepareQuery($sql, $params);

        $rs = $DB->Query($sql);

        if ($rs instanceof CDBResult AND $rs->SelectedRowsCount() > 0) {
            while ($arElement = $rs->Fetch()) {
                $dataProvider[] = $arElement;
            }
        }

        return $dataProvider;
    }

    /**
     * Выполнить SELECT запрос c LIMIT к базе данных и вернуть объект CDBResult
     *
     * @param string $sql
     * @param array $params
     * @param integer $page
     * @param integer $limit
     * @return CDBResult|false
     */
    public static function findAllLimit($sql, $params = array(), $page = 1, $limit = 10)
    {
        global $DB;

        $sql = self::_prepareQuery($sql, $params);

        if (preg_match('/^(\s* SELECT)(.*)/six', $sql, $m)) {
            $sql = $m[1] . ' SQL_CALC_FOUND_ROWS' . $m[2];;
        }

        $page = intval($page) > 1 ? intval($page) - 1 : 0;

        $sql .= PHP_EOL . ' LIMIT ' . ($page * $limit) . ', ' . $limit;

        $rs = $DB->Query($sql);
        $rsTotal = $DB->Query('SELECT FOUND_ROWS()');

        if ($rs instanceof CDBResult AND $rs->SelectedRowsCount() > 0) {
            if ($result = $rsTotal->Fetch()) {
                $rs->NavRecordCount = array_pop($result);

                $rs->NavPageCount = ceil($rs->NavRecordCount / $limit);
                $rs->NavPageNomer = $page + 1;
                $rs->NavPageSize = $limit;
            }

            return $rs;
        }

        return false;
    }

    /**
     * Возвращает значения ключа в заданном массиве
     *
     * @param string $key Ключ или ключи точку
     * Например, 'Media.Foto.thumbsize' преобразуется в ['Media']['Foto']['thumbsize']
     * @param array $array Массив значений
     * @param mixed $defaultValue Значение, возвращаемое в случае отсутствия ключа
     * @return mixed
     */
    private static function _getValueByComplexKeyFromArray($key, $array, $defaultValue = null)
    {
        if (strpos($key, '.') === false) {
            return (isset($array[$key])) ? $array[$key] : $defaultValue;
        }

        $keys = explode('.', $key);

        if (!isset($array[$keys[0]])) {
            return $defaultValue;
        }

        $value = $array[$keys[0]];
        unset($keys[0]);

        foreach ($keys as $k) {
            if (!isset($value[$k]) && !array_key_exists($k, $value)) {
                return $defaultValue;
            }
            $value = $value[$k];
        }

        return $value;
    }

    /**
     * Подготовка запроса к выполнению, замена плейсхолдеров
     *
     * @param string $sql
     * @param array $params
     * @return string
     */
    private static function _prepareQuery($sql, $params = array())
    {
        if (is_array($params) AND !empty($params)) {
            $params = array_map(
                function ($val) {
                    $val = ($val === false) ? 'NULL' : '"' . CDatabase::ForSql($val) . '"';
                    return $val;
                },
                $params
            );

            $sql = str_replace(array_keys($params), array_values($params), $sql);
        }

        return $sql;
    }
}
