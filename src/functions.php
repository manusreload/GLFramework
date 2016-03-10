<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 13/1/16
 * Time: 17:40
 */


function print_debug($info)
{
    echo "<pre>";
    foreach(func_get_args() as $arg)
    {
        print_r($arg);
        echo "\n";
    }

    die();
}
function fix_date($date)
{
    if($date != "")
    {
        if(preg_match("#([0-9]{2})/([0-9]{2})/([0-9]{4})#", $date, $matches))
        {
            return $matches[3] . "-" . $matches[2] . "-" . $matches[1];
        }
        $date = str_replace("/", "-", $date);
        return date("Y-m-d", strtotime($date));
    }
    return $date;
}

function fix_decimal($number)
{
    if(preg_match("#[0-9,.]+#", $number, $matches))
    {
        $number = str_replace(".", "", $number);
        $number = str_replace(",", ".", $number);
    }
    return $number;
}

function reArrayFiles(&$file_post) {

    $file_ary = array();
    $file_count = count($file_post['name']);
    $file_keys = array_keys($file_post);

    for ($i=0; $i<$file_count; $i++) {
        foreach ($file_keys as $key) {
            $file_ary[$i][$key] = $file_post[$key][$i];
        }
    }

    return $file_ary;
}

function file_get_php_classes($filepath) {
    $php_code = file_get_contents($filepath);
    $classes = get_php_classes($php_code);
    return $classes;
}

function get_php_classes($php_code) {
    $classes = array();
    $namespace = "";
    $namespaceBool = false;
    $tokens = token_get_all($php_code);
    $count = count($tokens);
    for ($i = 2; $i < $count; $i++) {
        if (   $tokens[$i - 2][0] == T_CLASS
            && $tokens[$i - 1][0] == T_WHITESPACE
            && $tokens[$i][0] == T_STRING) {

            $class_name = $namespace . $tokens[$i][1];
            $classes[] = $class_name;
        }

        if (   $tokens[$i][0] == T_NAMESPACE
            //&& $tokens[$i - 1][0] == T_WHITESPACE
            //&& $tokens[$i][0] == T_STRING
            ) {
            $namespaceBool = true;
        }
        if($tokens[$i] == ';')
        {
            $namespaceBool = false;
        }
        if($namespaceBool && $tokens[$i][0] == T_STRING)
        {
            $namespace .= $tokens[$i][1] . "\\";
        }
    }
    return $classes;
}

function array_merge_recursive_ex(array & $array1, array & $array2)
{
    $merged = $array1;

    foreach ($array2 as $key => & $value)
    {
        if (is_array($value) && isset($merged[$key]) && is_array($merged[$key]))
        {
            $merged[$key] = array_merge_recursive_ex($merged[$key], $value);
        } else if (is_numeric($key))
        {
            if (!in_array($value, $merged))
                $merged[] = $value;
        } else
            $merged[$key] = $value;
    }

    return $merged;
}

/**
 * @param $name
 *
 * Syntax: SpaceName\ClassName::method
 * Syntax: SpaceName\ClassName$$staticMethod
 * @return array
 */
function instance_method($name)
{
    if(strpos($name, "::") !== FALSE)
    {
        $split = explode("::", $name);
        return array(new $split[0](), $split[1]);
    }
    if(strpos($name, "$$") !== FALSE)
    {
        $split = explode("$$", $name);
        return $split[0] . "::" . $split[1];
    }
    return $name;
}

function today($format = "Y-m-d")
{
    return date($format);
}

function now($format = "Y-m-d H:i:s")
{
    return date($format);
}