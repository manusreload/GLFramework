<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 18/2/16
 * Time: 17:30
 */

namespace GLFramework\Model;


use GLFramework\Model;

class UserPage extends Model
{
    var $id;
    var $id_user;
    var $id_page;

    protected $table_name = "user_page";
    protected $definition = array(
        'index' => 'id',
        'fields' => array(
            'id_user' => "int(11)",
            'id_page' => "int(11)",
        )
    );

    public function exists()
    {
        if(!parent::exists())
        {
            return count($this->db->select("SELECT id FROM {$this->table_name} WHERE id_user = {$this->id_user} AND id_page = {$this->id_page}")) > 0;

        }
        return true;
    }


}