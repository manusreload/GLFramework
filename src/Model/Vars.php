<?php
/**
 * Created by PhpStorm.
 * User: mmuno
 * Date: 29/06/2016
 * Time: 16:47
 */

namespace GLFramework\Model;

use GLFramework\Model;

/**
 * Class Vars
 *
 * @package GLFramework\Model
 */
class Vars extends Model
{
    var $key;
    var $value;
    protected $table_name = 'vars';
    protected $definition = array(
        'index' => array('field' => 'key', 'type' => 'varchar(255)'),
        'fields' => array(
            'value' => 'varchar(128)',
            'updated_at' => 'datetime',
            'created_at' => 'datetime',
        )
    );

    /**
     * TODO
     *
     * @param $key
     * @param null $default
     * @return null
     */
    public static function getVar($key, $default = null)
    {
        $var = new Vars($key);
        return $var->value ?: $default;
    }

    /**
     * TODO
     *
     * @param $key
     * @param $value
     * @return bool
     */
    public static function setVar($key, $value)
    {
        $var = new Vars($key);
        $var->key = $key;
        $var->value = $value;
        return $var->save();
    }

    /**
     * TODO
     *
     * @return array
     */
    public static function getArray()
    {
        $var = new Vars();
        $list = array();
        foreach ($var->get_all()->getModels() as $item) {
            $list[$item->key] = $item->value;
        }
        return $list;
    }
}
