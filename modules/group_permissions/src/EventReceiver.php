<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 2/03/16
 * Time: 10:17
 */

namespace GLFrameworkModules;


use GLFramework\Controller\AuthController;
use GLFramework\Events;
use GLFramework\Model\Page;
use GLFramework\Model\User;

class EventReceiver
{

    public function beforeControllerRun($controller)
    {
        if($controller instanceof AuthController)
        {

        }
    }
    public function isUserAllowed($controller, $user)
    {
        return self::isGroupAllowed($controller, $user);
    }

    /**
     * @param $controller
     * @param $user User
     * @return bool
     */
    public static function isGroupAllowed($controller, $user)
    {
        $context = Events::getContext();
        $config = $context->getConfig();
        $page = new Page();
        $page = $page->get_by_controller($controller)->getModel();
        if($page->id > 0)
        {
            $group = new \Group();
            $groupPages = new \GroupPage();
            $groups = $group->getByUser($user);
            foreach($groups->getModels() as $group)
            {
                $result = $groupPages->get(array('id_group' => $group->id, 'id_page' => $page->id));
                if($result->count() > 0) return ALLOW_USER;
            }
        }
        return isset($config['allowDefault'])?(!$config['allowDefault']?DISALLOW_USER:""):null;
    }

    public function getAdminControllers()
    {
        return 'GLFramework\Modules\GroupPermissions\groups';
    }
}