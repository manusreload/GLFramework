<?php
/**
 * Created by PhpStorm.
 * User: mmuno
 * Date: 29/06/2016
 * Time: 16:47
 */

namespace GLFramework\Model;


use GLFramework\Model;

class Vars extends Model
{
    var $key;
    var $value;
    protected $table_name = "vars";
    protected $definition = array(
        'index' => array('field' => 'key', 'type' => "varchar(255)"),
        'fields' => array(
            'value' => "varchar(255)",
            'updated_at' => "datetime",
            'created_at' => "datetime",
        )
    );
    
    public static function getVar($key, $default = null)
    {
        $var = new Vars($key);
        return $var->value?:$default;
    }

    public static function setVar($key, $value)
    {
        $var = new Vars($key);
        $var->key = $key;
        $var->value = $value;
        return $var->save();
    }
    
}