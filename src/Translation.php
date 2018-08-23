<?php
/**
 * Created by PhpStorm.
 * User: mmunoz
 * Date: 23/08/18
 * Time: 13:21
 */

namespace GLFramework;


use Symfony\Component\Translation\Translator;

class Translation
{

    private $translator;

    /**
     * Translation constructor.
     */
    public function __construct($config = false)
    {
        if(!$config) {
            $config = Bootstrap::getSingleton()->getConfig();
        }
        $this->translator = new Translator();
    }

    public function setLanguage($lang) {
        $this->translator = new Translator($lang);
    }


}