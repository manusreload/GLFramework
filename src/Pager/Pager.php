<?php
/**
 * Created by PhpStorm.
 * User: mmunoz
 * Date: 8/10/18
 * Time: 9:28
 */

namespace GLFramework\Pager;


class Pager
{

    public function register($id, $template) {
        $fs = new Filesystem($id, 'pager');
        if(!$fs->exists()) {
            $fs->write($template);
        }
    }

    public function render($class, $id, $options = []) {
        $fs = new Filesystem($id, 'pager');
        $instance = new $class();
        if(($instance instanceof Controller)) {
            if($instance instanceof PagerHandler);
            $instance->setContext($options);
            $options = $instance->getContext();
            $count = $instance->getItemsCount();
            $data = $instance->pagerHandler();
            $context = is_array($data)?$data:[];
            $context['_pages'] = $count > 0?floor($count / $options['limit']):0;
            $context['_count'] = $count;
            $context['_template'] = $fs->read();
            $context['_options'] = $options;
            $view = new \GLFramework\View($instance);
            $view->getTwig()->addExtension(new \Twig_Extension_StringLoader());
            return $view->getTwig()->loadTemplate("templates/pager-handler.twig")->render($context);
        }
        return "Invalid Implementation!";
    }
}