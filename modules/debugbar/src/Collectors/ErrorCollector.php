<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 27/5/16
 * Time: 16:31
 */

namespace GLFramework\Modules\Debugbar\Collectors;


use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\ExceptionsCollector;
use DebugBar\DataCollector\Renderable;
use GLFramework\Modules\Debugbar\GeneratedError;

class ErrorCollector extends DataCollector implements Renderable
{
    protected $errors = array();
    protected $chainErrors = false;

    /**
     * Adds an exception to be profiled in the debug bar
     *
     * @param GeneratedError $e
     */
    public function addError(GeneratedError $e)
    {
        $this->errors[] = $e;
        if ($this->chainErrors && $previous = $e->getPrevious()) {
            $this->addError($previous);
        }
    }

    /**
     * Configure whether or not all chained exceptions should be shown.
     *
     * @param bool $chainExceptions
     */
    public function setChainErrors($chainErrors = true)
    {
        $this->chainErrors = $chainErrors;
    }

    /**
     * Returns the list of exceptions being profiled
     *
     * @return GeneratedError[]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    public function collect()
    {
        return array(
            'count' => count($this->errors),
            'errors' => array_map(array($this, 'formatExceptionData'), $this->errors)
        );
    }

    /**
     * Returns exception data as an array
     *
     * @param \Throwable $e
     * @return array
     */
    public function formatExceptionData(GeneratedError $e)
    {
        $filePath = $e->getFile();
        if ($filePath && file_exists($filePath)) {
            $lines = file($filePath);
            $start = $e->getLine() - 4;
            $lines = array_slice($lines, $start < 0 ? 0 : $start, 7);
        } else {
            $lines = array("Cannot open the file ($filePath) in which the exception occurred ");
        }

        return array(
//            'type' => get_class($e),
            'type' => '',
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $filePath,
            'line' => $e->getLine(),
            'surrounding_lines' => $lines
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'errors';
    }

    /**
     * @return array
     */
    public function getWidgets()
    {
        return array(
            'errors' => array(
                'icon' => 'alert',
                'widget' => 'PhpDebugBar.Widgets.ExceptionsWidget',
                'map' => 'errors.errors',
                'default' => '[]'
            ),
            'errors:badge' => array(
                'map' => 'errors.count',
                'default' => 'null'
            )
        );
    }
}