<?php
/**
 * Created by PhpStorm.
 * User: mmunoz
 * Date: 2/10/18
 * Time: 15:58
 */

namespace GLVentas;


trait PagerHandler
{
    private $options;
    private $context;
    public $table;
    abstract public function pagerHandler();
    abstract public function getItemsCount();
    abstract public function getFilter();


    /**
     * @return mixed
     */
    public function getContext()
    {
        return array_merge($this->options, $this->context);
    }

    /**
     * @param mixed $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    public function getDefaultOptions() {
        return ['limit' => 10, 'page' => 0];
    }

    public function getOptions() {
        return $this->options;
    }

    public function getLeft($options) {
        return $options['page'] * $options['limit'];
    }
    public function getRight($options) {
        return $options['limit'];
    }

    protected function pagerSetup($options) {
        $this->options = array_merge($this->getDefaultOptions(), $options);
    }

}