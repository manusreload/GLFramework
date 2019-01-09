<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 2019-01-09
 * Time: 16:35
 */
require_once "DummyDatabaseConnector.php";
class ModelTest extends \PHPUnit\Framework\TestCase
{
//    /**
//     * @return DummyDatabaseConnector
//     */
//    public function testConnector() {
//        $connector = new DummyDatabaseConnector();
//        $this->assertInstanceOf(\GLFramework\Database\Connection::class, $connector);
//
//        return $connector;
//    }

    /**
     * @throws Exception
     */
    public function testConnectDatabase() {
        $bootstrap = BootstrapTest::testCreateBootstrap();
        $bootstrap->overrideConfig(__DIR__ . '/data/config.modules.yml');
        $bootstrap->init();
        $bootstrap->setDatabase(new \GLFramework\DatabaseManager());
        $bootstrap->getDatabase()->connectAndSelect();
        $bootstrap->getDatabase()->checkDatabaseStructure();
    }

    /**
     * @depends testConnectDatabase
     * @return SimpleModel
     * @throws Exception
     */
    public function testModelCreate() {

        $testModel = new SimpleModel();
        $testModel->field1 = "1";
        $testModel->field2 = "value2";
        $this->assertTrue($testModel->save(true) > 0);
        return $testModel;
    }

    /**
     * @depends testModelCreate
     * @param $model SimpleModel
     */
    public function testUpdate($model) {
        $this->assertTrue($model->id > 0);
        $model->field2 = "value2updated";

        $this->assertNotEquals($model->field2, $model->get($model->id)->getModel()->field2);
        $this->assertTrue($model->save() > 0);
        $model2 = new SimpleModel($model->id);
        $this->assertEquals($model->field2, $model2->field2);
    }

}