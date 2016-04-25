<?php

/**
 * Created by PhpStorm.
 * User: manus
 * Date: 25/04/16
 * Time: 10:09
 */
class APIAuthorization extends \GLFramework\Model
{

    protected $table_name = "api_authorization";
    protected $definition = array(
        'index' => 'id',
        'fields' => array(
            'id_user' => 'int(11)',
            'token' => 'varchar(64)',
            'date' => 'datetime',
            
        )
    );
}