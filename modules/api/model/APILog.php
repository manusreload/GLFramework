<?php
/**
 * Created by PhpStorm.
 * User: MMunoz
 * Date: 3/15/2019
 * Time: 09:52
 */

class APILog extends \GLFramework\Model
{
    var $id;
    var $id_api;
    var $endpoint;
    var $created_at;

    protected $table_name = "api_log";
    protected $definition = array(
        'index' => 'id',
        'fields' => array(
            'id_api' => "int(11)",
            'endpoint' => "varchar(128)",
            'created_at' => "datetime",
        )
    );

    /**
     * @param $apiAuthorization APIAuthorization
     * @param $endpoint string
     */
    public static function log($apiAuthorization, $endpoint) {
        $log = new APILog();
        $log->id_api = $apiAuthorization->id;
        $log->endpoint = $endpoint;
        $log->save();
    }
}