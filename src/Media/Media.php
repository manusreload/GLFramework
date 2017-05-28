<?php
/**
 * Created by PhpStorm.
 * User: mmunoz
 * Date: 20/12/16
 * Time: 15:41
 */

namespace GLFramework\Media;

use GLFramework\Bootstrap;
use GLFramework\Log;
use GLFramework\Module\ModuleManager;

/**
 * Class Media
 *
 * @package GLFramework\Media
 */
abstract class Media
{
    protected $source;
    protected $options = array();
    protected $file;

    /**
     * Media constructor.
     *
     * @param $source
     * @param array $options
     */
    public function __construct($source, array $options = array())
    {
        $this->source = $source;
        $this->options = $options;
        $this->file = realpath('.') . '/' . '$source';
        if (!file_exists($this->file)) {
            $module = ModuleManager::getInstance()->getRunningModule();
            $views = ModuleManager::getInstance()->getViews($module);
            foreach ($views as $view) {
                $path = $view . '/' . $source;
                if (file_exists($path)) {
                    $this->file = $path;
                    break;
                }
            }
        }
    }

    /**
     * TODO
     *
     * @return mixed
     */
    public function getBrowserCode()
    {
        if (file_exists($this->file)) {
            $src = Bootstrap::getSingleton()->toUrl($this->file);
        } else {
            $src = $this->source;
            Log::d("Media file not found: " . $src);
        }
        if (is_array($this->options)) {
            if (isset($this->options['version'])) {
                $src = $this->addParameterToURL($src, 'v', $this->options['version']);
            }
            if (isset($this->options['v'])) {
                $src = $this->addParameterToURL($src, 'v', $this->options['v']);
            }
            if (isset($this->options['hash'])) {
                if (is_file($this->file) && file_exists($this->file)) {
                    switch ($this->options['hash']) {
                        case 'md5':
                            $src = $this->addParameterToURL($src, 'h', md5_file($this->file));
                            break;
                        case 'sha1':
                        default:
                            $src = $this->addParameterToURL($src, 'h', sha1_file($this->file));
                            break;
                    }
                }
            }
        } elseif (is_numeric($this->options)) {
            $src = $this->addParameterToURL($src, 'v', $this->options);
        } elseif (is_string($this->options)) {
            $src = $this->addParameterToURL($src, 'o', $this->options);
        }

        return $this->get($src);
    }

    /**
     * TODO
     *
     * @param $url
     * @param $key
     * @param $value
     * @return string
     */
    public function addParameterToURL($url, $key, $value)
    {
        $key = urlencode($key);
        $value = urlencode($value);
        if (strrpos($url, '?') > 0) {
            $url .= "&$key=$value";
        } else {
            $url .= "?$key=$value";
        }

        return $url;
    }

    /**
     * TODO
     *
     * @param $src
     * @return mixed
     */
    abstract protected function get($src);
}
