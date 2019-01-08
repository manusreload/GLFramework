<?php

/**
 * Created by PhpStorm.
 * User: manus
 * Date: 1/03/16
 * Time: 15:42
 */
class Group extends \GLFramework\Model
{

    var $id;
    var $title;
    var $permissions;
    var $default;
    protected $table_name = "groups";
    protected $definition = array(
        'index' => 'id',
        'fields' => array(
            'title' => 'varchar(64)',
            'permissions' => 'varchar(64)',
            'default' => 'int(11)',

        )
    );

    public function count_pages()
    {
        $groupPages = new GroupPage();
        return $groupPages->get(array('id_group' => $this->id))->count();

    }

    /**
     * @param $user User
     * @return \GLFramework\ModelResult
     */
    public function getByUser($user)
    {
        $groups = explode(",", $user->privilegios);
        $groups = array_map('trim', $groups);
        return $this->build($this->db->select("SELECT * FROM `" . $this->getTableName() . "` WHERE permissions IN ('" . implode("','", $groups) ."') OR `default` = 1"));
    }

    public function search($label, $limit = -1)
    {
        return $this->build($this->db->select("SELECT * FROM " . $this->getTableName() . " WHERE title LIKE '%" . $label . "%'"));
    }
}