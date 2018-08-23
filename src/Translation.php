<?php
/**
 * Created by PhpStorm.
 * User: mmunoz
 * Date: 23/08/18
 * Time: 13:21
 */

namespace GLFramework;


use Symfony\Component\Translation\Loader\YamlFileLoader;
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
        $locale = 'es_ES';
        $cacheDir = null;
        $debug = false;
        $resources = [];
        if(isset($config['lang'])) {
            $locale = $config['lang']['locale']??$locale;
            $cacheDir = $config['lang']['cacheDir']??$cacheDir;
            $debug = $config['lang']['debug']??$debug;

//            if(isset($config['lang']['resources'])) {
//                $list = $config['lang']['resources'];
//                foreach ($list as $item) {
//                    $resources[] = $item;
//                }
//            }
        }
        $this->translator = new Translator($locale, null, $cacheDir, $debug);
        $this->translator->addLoader('yaml', new YamlFileLoader());
    }

    public function tr($id, array $parameters = array(), $domain = null, $locale = null) {
        return $this->translator->trans($id, $parameters, $domain, $locale);
    }

    public function addResource($path, $locale) {
        $this->translator->addResource('yaml', $path, $locale);
    }

}