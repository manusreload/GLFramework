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
        $date = str_replace("/", "-", $date);
        return date("Y-m-d", strtotime($date));
    }
    return $date;
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
    $tokens = token_get_all($php_code);
    $count = count($tokens);
    for ($i = 2; $i < $count; $i++) {
        if (   $tokens[$i - 2][0] == T_CLASS
            && $tokens[$i - 1][0] == T_WHITESPACE
            && $tokens[$i][0] == T_STRING) {

            $class_name = $namespace . $tokens[$i][1];
            $classes[] = $class_name;
        }

        if (   $tokens[$i - 2][0] == T_NAMESPACE
            && $tokens[$i - 1][0] == T_WHITESPACE
            && $tokens[$i][0] == T_STRING) {
            $namespace = $tokens[$i][1] . "\\";
        }
    }
    return $classes;
}