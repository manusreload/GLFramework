<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 2019-01-09
 * Time: 16:36
 */

class SimpleModel extends \GLFramework\Model
{
    public $id;
    public $field1;
    public $field2;
    protected $table_name = 'simple';
    protected $definition = array(
        'index' => 'id',
        'fields' => array(
            'field1' => 'int(11)',
            'field2' => 'varchar(16)',
        )
    );


}