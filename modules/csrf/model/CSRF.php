<?php

/**
 * Created by PhpStorm.
 * User: manus
 * Date: 18/04/16
 * Time: 11:35
 */
class CSRF extends \GLFramework\Model
{

    var $id;
    var $token;
    var $used;
    protected $table_name = "csrf_token";
    protected $definition = array(
        'index' => 'id',
        'fields' => array(
            'token' => 'varchar(64)',
            'used' => 'int(1)'
        )
    );

    public static function generate()
    {
        $csrf = new CSRF();
        $csrf->token = random_str(32);
        $csrf->used = 0;
        $csrf->save(true);
        return $csrf;
    }
}