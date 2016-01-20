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
    $date = str_replace("/", "-", $date);
    return date("Y-m-d", strtotime($date));
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

