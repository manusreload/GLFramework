<?php

/**
 * Created by PhpStorm.
 * User: manus
 * Date: 28/5/16
 * Time: 16:17
 */
class UserRecovery extends \GLFramework\Model
{

    var $id;
    var $id_user;
    var $token;
    var $time;

    protected $table_name = "user_recovery";
    
    protected $definition = array(
        'index' => 'id',
        'fields' => array(
            'id_user' => "int(11)",
            'token' => "varchar(64)",
            'time' => "int(11)",
        )
    );
    protected $models = array(
        'id_user' => array('name' => 'user', 'model' => 'User'),
    );

    /**
     * @param $user User
     * @return $this
     */
    public function generateNew($user)
    {
        $this->id_user = $user->id;
        $this->token = random_str(64);
        $this->time = time();
        return $this;
    }


}