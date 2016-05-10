<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 9/05/16
 * Time: 16:42
 */
namespace GLFramework\Modules\Debugbar\Collectors;

use DebugBar\DataCollector\PDO\TraceablePDOStatement;
use DebugBar\DataCollector\PDO\TracedStatement;
use DebugBar\DataCollector\TimeDataCollector;
use PDO;
use PDOException;

class TraceablePDO extends \DebugBar\DataCollector\PDO\TraceablePDO
{
    var $timer;
    public function __construct(PDO $pdo, TimeDataCollector $timer)
    {
        parent::__construct($pdo);
        $this->timer = $timer;
    }


    protected function profileCall($method, $sql, array $args)
    {
        $id = md5($sql);
        $trace = new TracedStatement($sql);
        $trace->start();
        $this->timer->startMeasure($id, $sql);

        $ex = null;
        try {
            $result = call_user_func_array(array($this->pdo, $method), $args);
        } catch (\PDOException $e) {
            $ex = $e;
        }

        if ($this->pdo->getAttribute(PDO::ATTR_ERRMODE) !== PDO::ERRMODE_EXCEPTION && $result === false) {
            $error = $this->pdo->errorInfo();
            $ex = new PDOException($error[2], $error[0]);
        }
        $count = 0;
        if($result instanceof TraceablePDOStatement)
        {
            $count = $result->rowCount();
        }
        $trace->end($ex, $count);
        $this->timer->stopMeasure($id, $sql);
        $this->addExecutedStatement($trace);

        if ($this->pdo->getAttribute(PDO::ATTR_ERRMODE) === PDO::ERRMODE_EXCEPTION && $ex !== null) {
            throw $ex;
        }
        return $result;
    }
}