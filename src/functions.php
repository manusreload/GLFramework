<?php
/**
 *     GLFramework, small web application framework.
 *     Copyright (C) 2016.  Manuel Muñoz Rosa
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation, either version 3 of the License, or
 *     (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU General Public License for more details.
 *
 *     You should have received a copy of the GNU General Public License
 *     along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Created by PhpStorm.
 * User: manus
 * Date: 13/1/16
 * Time: 17:40
 */

/**
 * Muestra la información pasada como argumentos, y termina la ejecución del programa
 *
 * @param $info
 */
function print_debug($info)
{
    forward_static_call_array(array('Kint', 'dump'), func_get_args());

    die();
}

/**
 * TODO
 *
 * @param $n_iters
 * @param $info
 */
function print_debug_iters($n_iters, $info)
{
    echo '<pre>';
    foreach (func_get_args() as $arg) {
        print_r($arg);
        echo "\n";
    }

    if ($n_iters == 0) {
        die();
    }
}

/**
 * TODO
 *
 * @param $info
 * @param int $limit
 */
function print_brief_debug($info, $limit = 1)
{
    echo "<pre>";
    print_array($info, $limit);

    //    die();
}

/**
 * TODO
 *
 * @param $array
 * @param int $depth
 * @param int $indentation
 */
function print_array($array, $depth = 1, $indentation = 0)
{
    if (is_array($array)) {
        echo "Array(\n";
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if ($depth) {
                    echo 'max depth reached.';
                } else {
                    for ($i = 0; $i < $indentation; $i++) {
                        echo '&nbsp;&nbsp;&nbsp;&nbsp;';
                    }
                    echo $key . '=Array(';
                    print_array($value, $depth - 1, $indentation + 1);
                    for ($i = 0; $i < $indentation; $i++) {
                        echo '&nbsp;&nbsp;&nbsp;&nbsp;';
                    }
                    echo ');';
                }
            } else {
                for ($i = 0; $i < $indentation; $i++) {
                    echo '&nbsp;&nbsp;&nbsp;&nbsp;';
                }
                echo $key . '=>' . $value . "\n";
            }
        }
        echo ");\n";
    } elseif (is_object($array)) {
        echo get_class($array) . "\n";
    } else {
        var_dump($array);
    }
}

/**
 *
 * Soporta fechas del estilo:
 *      - DD/MM/AAAA
 *      - MM-DD-AA
 *      - YYYY-MM-DD
 *
 * @param $date
 * @return false|mixed|string
 */
function fix_date($date)
{
    $date = trim($date);
    if ($date !== '') {
        if (preg_match('#(\d{2})/(\d{2})/(\d{4})#', $date, $matches)) {
            return $matches[3] . '-' . $matches[2] . '-' . $matches[1];
        }

        if (preg_match('#(\d{2})-(\d{2})-(\d{2})#', $date, $matches)) {
            return '20' . $matches[3] . '-' . $matches[1] . '-' . $matches[2];
        }
        $date = str_replace('/', '-', $date);
        return date('Y-m-d', strtotime($date));
    }
    return $date;
}

/**
 * TODO
 *
 * @param $number
 * @return mixed
 */
function fix_decimal($number)
{
    if (preg_match('#[0-9,.]+#', $number, $matches)) {
        $number = str_replace('.', '', $number);
        $number = str_replace(',', '.', $number);
    }
    return $number;
}

/**
 * TODO
 *
 * @param $date
 * @param string $format
 * @return string
 */
function fix_date_format($date, $format = 'd-m-Y')
{
    $res = date_create_from_format($format, $date);
    if ($res) {
        return $res->format('Y-m-d');
    }
    return $date;
}

/**
 * TODO
 *
 * @param $file_post
 * @return array
 */
function reArrayFiles(&$file_post)
{
    $file_ary = array();
    $file_count = count($file_post['name']);
    $file_keys = array_keys($file_post);

    for ($i = 0; $i < $file_count; $i++) {
        foreach ($file_keys as $key) {
            $file_ary[$i][$key] = $file_post[$key][$i];
        }
    }

    return $file_ary;
}

/**
 * TODO
 *
 * @param $array
 * @return array
 */
function reArrayPost($array)
{
    $keys = array();
    $result = array();
    foreach ($array as $key => $value) {
        $keys[] = $key;
    }
    foreach ($keys as $key) {
        foreach ($array[$key] as $k => $v) {
            if (!isset($result[$k])) {
                $result[$k] = array();
            }
            $result[$k][$key] = $v;
        }
    }
    return $result;
}

/**
 * TODO
 *
 * @param $filepath
 * @return array
 */
function file_get_php_classes($filepath)
{
    $php_code = file_get_contents($filepath);
    $classes = get_php_classes($php_code);
    return $classes;
}

/**
 * TODO
 *
 * @param $php_code
 * @return array
 */
function get_php_classes($php_code)
{
    $classes = array();
    $namespace = '';
    $namespaceBool = false;
    $tokens = token_get_all($php_code);
    $count = count($tokens);
    for ($i = 0; $i < $count; $i++) {
        if ($i >= 2 && $tokens[$i - 2][0] === T_CLASS && $tokens[$i - 1][0] === T_WHITESPACE && $tokens[$i][0] === T_STRING) {
            $class_name = $namespace . $tokens[$i][1];
            $classes[] = $class_name;
        }

        if ($tokens[$i][0] === T_NAMESPACE
            //&& $tokens[$i - 1][0] === T_WHITESPACE
            //&& $tokens[$i][0] === T_STRING
        ) {
            $namespaceBool = true;
        }
        if ($tokens[$i] === ';') {
            $namespaceBool = false;
        }
        if ($namespaceBool && $tokens[$i][0] === T_STRING) {
            $namespace .= $tokens[$i][1] . '\\';
        }
    }
    return $classes;
}

/**
 * TODO
 *
 * @param array $array1
 * @param array $array2
 * @return array
 */
function array_merge_recursive_ex(array & $array1, array & $array2)
{
    $merged = $array1;

    foreach ($array2 as $key => & $value) {
        if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
            $merged[$key] = array_merge_recursive_ex($merged[$key], $value);
        } elseif (is_numeric($key)) {
            if (!in_array($value, $merged)) {
                $merged[] = $value;
            }
        } else {
            $merged[$key] = $value;
        }
    }

    return $merged;
}

/**
 * @param $name
 *
 * Syntax: SpaceName\ClassName->method
 * Syntax: SpaceName\ClassName::staticMethod
 * @param array $cache
 * @param array $instanceParams
 * @return array
 */
function instance_method($name, &$cache = array(), $instanceParams = array())
{
    if (!$cache) {
        $cache = array();
    }
    if (strpos($name, '->') !== false) {
        $split = explode('->', $name);
        $instance = null;
        if (!isset($cache[$split[0]])) {
            if (class_exists($split[0])) {
                $rf = new ReflectionClass($split[0]);
                $instance = $rf->getConstructor() ? $rf->newInstanceArgs($instanceParams) : $rf->newInstance();
                //            $instance = new $split[0]();
                $cache[$split[0]] = $instance;
            } else {
                \GLFramework\Log::d('Class ' . $split[0] . ' not found! While try to instance ' . $name);
            }
        }
        $instance = $cache[$split[0]];
        return array($instance, $split[1]);
    }
    if (strpos($name, '::') !== false) {
        $split = explode('::', $name);
        return $split[0] . '::' . $split[1];
    }
    return $name;
}

/**
 * TODO
 *
 * @param string $format
 * @return false|string
 */
function today($format = 'Y-m-d')
{
    return date($format);
}

/**
 * TODO
 *
 * @param string $format
 * @return false|string
 */
function now($format = 'Y-m-d H:i:s')
{
    return date($format);
}

/**
 * TODO
 *
 * @param $text
 * @return string
 */
function e($text)
{
    return htmlentities($text);
}

/**
 * TODO
 *
 * @param $length
 * @param string $keyspace
 * @return string
 */
function random_str($length, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
{
    $str = '';
    $max = mb_strlen($keyspace, '8bit') - 1;
    for ($i = 0; $i < $length; ++$i) {
        $str .= $keyspace[mt_rand(0, $max)];
    }
    return $str;
}

/**
 * TODO
 *
 * @param $module
 * @return bool
 */
function is_module_enabled($module)
{
    return \GLFramework\Module\ModuleManager::exists($module);
}

if (!function_exists('http_response_code')) {
    /**
     * TODO
     *
     * @param null $code
     * @return int|mixed|null
     */
    function http_response_code($code = null)
    {
        if ($code !== null) {
            switch ($code) {
                case 100:
                    $text = 'Continue';
                    break;
                case 101:
                    $text = 'Switching Protocols';
                    break;
                case 200:
                    $text = 'OK';
                    break;
                case 201:
                    $text = 'Created';
                    break;
                case 202:
                    $text = 'Accepted';
                    break;
                case 203:
                    $text = 'Non-Authoritative Information';
                    break;
                case 204:
                    $text = 'No Content';
                    break;
                case 205:
                    $text = 'Reset Content';
                    break;
                case 206:
                    $text = 'Partial Content';
                    break;
                case 300:
                    $text = 'Multiple Choices';
                    break;
                case 301:
                    $text = 'Moved Permanently';
                    break;
                case 302:
                    $text = 'Moved Temporarily';
                    break;
                case 303:
                    $text = 'See Other';
                    break;
                case 304:
                    $text = 'Not Modified';
                    break;
                case 305:
                    $text = 'Use Proxy';
                    break;
                case 400:
                    $text = 'Bad Request';
                    break;
                case 401:
                    $text = 'Unauthorized';
                    break;
                case 402:
                    $text = 'Payment Required';
                    break;
                case 403:
                    $text = 'Forbidden';
                    break;
                case 404:
                    $text = 'Not Found';
                    break;
                case 405:
                    $text = 'Method Not Allowed';
                    break;
                case 406:
                    $text = 'Not Acceptable';
                    break;
                case 407:
                    $text = 'Proxy Authentication Required';
                    break;
                case 408:
                    $text = 'Request Time-out';
                    break;
                case 409:
                    $text = 'Conflict';
                    break;
                case 410:
                    $text = 'Gone';
                    break;
                case 411:
                    $text = 'Length Required';
                    break;
                case 412:
                    $text = 'Precondition Failed';
                    break;
                case 413:
                    $text = 'Request Entity Too Large';
                    break;
                case 414:
                    $text = 'Request-URI Too Large';
                    break;
                case 415:
                    $text = 'Unsupported Media Type';
                    break;
                case 500:
                    $text = 'Internal Server Error';
                    break;
                case 501:
                    $text = 'Not Implemented';
                    break;
                case 502:
                    $text = 'Bad Gateway';
                    break;
                case 503:
                    $text = 'Service Unavailable';
                    break;
                case 504:
                    $text = 'Gateway Time-out';
                    break;
                case 505:
                    $text = 'HTTP Version not supported';
                    break;
                default:
                    exit('Unknown http status code \'' . htmlentities($code) . '\'');
                    break;
            }

            $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');

            header($protocol . ' ' . $code . ' ' . $text);

            $GLOBALS['http_response_code'] = $code;
        } else {
            $code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);
        }

        return $code;
    }
}

/**
 * TODO
 *
 * @param $method
 * @param $uri
 * @param array $fields
 * @param array $header
 * @return mixed
 */
function custom_http_request($method, $uri, $fields = array(), $header = array())
{
    $fields_string = '';
    if (is_array($fields)) {
        foreach ($fields as $key => $value) {
            $fields_string .= $key . '=' . $value . '&';
        }
        rtrim($fields_string, '&');
    } else {
        $fields_string = $fields;
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $uri);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}

/**
 * TODO
 *
 * @param $url
 * @param $fields
 * @param array $header
 * @return mixed
 */
function post($url, $fields, $header = array())
{
    $fields_string = '';
    foreach ($fields as $key => $value) {
        $fields_string .= $key . '=' . $value . '&';
    }
    rtrim($fields_string, '&');
    //open connection
    $ch = curl_init();

    //set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, count($fields));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    //execute post
    $result = curl_exec($ch);

    //close connection
    curl_close($ch);
    return $result;
}

/**
 * TODO
 *
 * @param $url
 * @param array $header
 * @return mixed
 */
function get($url, $header = array())
{
    //open connection
    $ch = curl_init();

    //set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    //execute post
    $result = curl_exec($ch);

    //close connection
    curl_close($ch);
    return $result;
}

/**
 * TODO
 *
 * @param $url
 * @return string
 */
function fix_url($url)
{
    if (strpos($url, 'http') === false) {
        $url = 'http://' . $_SERVER['HTTP_HOST'] . $url;
    }
    return $url;
}

/**
 * TODO
 *
 * @param Exception $ex
 * @param int $i
 */
function display_exception(Exception $ex, $i = 1)
{
    echo '<h3>(' . $i . ') ' . $ex->getMessage() . '</h3> at ' . $ex->getFile() . ':' . $ex->getLine();
    echo '<pre>' . $ex->getTraceAsString() . '</pre><br>';

    if ($ex->getPrevious()) {
        display_exception($ex->getPrevious(), $i + 1);
    }
}

/**
 * TODO
 *
 * @return array
 */
function time_elapsed_default_translation()
{
    return array(
        'few' => 'unos segundos',
        'plural' => 's',
        'seconds' => '%d segundo%s',
        'minutes' => '%d minuto%s',
        'hours' => '%d hora%s',
        'days' => '%d dias%s',
    );
}

/**
 * TODO
 *
 * @param $start
 * @param null $end
 * @param array $translation
 * @return string
 */
function time_elapsed($start, $end = null, $translation = array())
{
    if (empty($translation)) {
        $translation = time_elapsed_default_translation();
    }
    if (!$end) {
        $end = time();
    }
    $seconds = $end - $start;
    if ($seconds <= 15) {
        $key = 'few';
    } else {
        $keys = array('seconds' => 1, 'minutes' => 60, 'hours' => 24 * 60, 'days');
        $current = 0;
    }

    return sprintf($translation[$key], $seconds);
}

/**
 * TODO
 *
 * @param $function
 * @return string
 */
function function_dump($function)
{
    if ($function instanceof Closure) {
        return function_closure_dump($function);
    }
    if (is_array($function)) {
        return get_class($function[0]) . '::' . $function[1];
    }
}

/**
 * TODO
 *
 * @param $closure
 * @return string
 */
function function_closure_dump($closure)
{
    $str = 'function (';
    $r = new ReflectionFunction($closure);
    $params = array();
    foreach ($r->getParameters() as $p) {
        $s = '';
        if ($p->isArray()) {
            $s .= 'array ';
        } elseif ($p->getClass()) {
            $s .= $p->getClass()->name . ' ';
        }
        if ($p->isPassedByReference()) {
            $s .= '&';
        }
        $s .= '$' . $p->name;
        if ($p->isOptional()) {
            $s .= ' = ' . var_export($p->getDefaultValue(), true);
        }
        $params [] = $s;
    }
    $str .= implode(', ', $params);
    $str .= '){' . PHP_EOL;
    $lines = file($r->getFileName());
    for ($l = $r->getStartLine(); $l < $r->getEndLine(); $l++) {
        $str .= $lines[$l];
    }
    return $str;
}

/**
 * TODO
 *
 * @param $date
 * @param string $format
 * @return false|string
 */
function fecha($date, $format = 'd/m/Y')
{
    return date($format, strtotime($date));
}

function fix_folder($folder)
{
    if(strpos("/", $folder)  === 0)
    {
        return $folder;
    }
    return '/' . $folder;
}
