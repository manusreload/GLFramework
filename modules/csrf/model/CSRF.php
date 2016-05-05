<?php

/**
 * Created by PhpStorm.
 * User: manus
 * Date: 18/04/16
 * Time: 11:35
 */
class CSRF extends \GLFramework\Model
{
    private static $clean = false;
    var $id;
    var $token;
    var $used;
    var $time;
    protected $table_name = "csrf_token";
    protected $definition = array(
        'index' => 'id',
        'fields' => array(
            'token' => 'varchar(64)',
            'used' => 'int(1)',
            'time' => 'int(11)'
        )
    );

    public static function generate()
    {
        $csrf = new CSRF();
        if(!self::$clean)
        {
            $csrf->db->exec("DELETE FROM {$csrf->getTableName()} WHERE `time` < " . (time() - 60 * 60));
            self::$clean = true;
        }
        $csrf->token = random_str(32);
        $csrf->used = 0;
        $csrf->time = time();
        $csrf->save(true);
        return $csrf;
    }
}