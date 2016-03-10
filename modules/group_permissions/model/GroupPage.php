<?php

/**
 * Created by PhpStorm.
 * User: manus
 * Date: 1/03/16
 * Time: 15:45
 */
class GroupPage extends \GLFramework\Model
{

    var $id;
    var $id_group;
    var $id_page;

    protected $table_name = "groups_pages";
    protected $definition = array(
        'index' => 'id',
        'fields' => array(
            'id_group' => 'int(11)',
            'id_page' => 'int(11)',
        )
    );
}