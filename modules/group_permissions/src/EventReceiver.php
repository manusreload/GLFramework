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
    /**
     * @param \GroupPage[] $cache 
     */
    private static $cache = [];
    private static $groupsUserCache = [];
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
     * @param \User $user
     * @return bool
     */
    public static function isGroupAllowed($controller, $user)
    {
        $context = Events::getContext();
        $config = $context->getConfig();
        $page = new Page();
        $page = $page->get_by_controller($controller)->getModel();
        if($page instanceof Page && $page->id > 0)
        {
            $groups = self::getGroupsByUser($user);
            foreach($groups->getModels() as $group)
            {
                $result = self::isGroupPages($group->id, $page->id);
                if($result) {
                    return ALLOW_USER;
                }
                // $result = $groupPages->get(array('id_group' => $group->id, 'id_page' => $page->id));
                // if($result->count() > 0) return ALLOW_USER;
            }
        }
        return isset($config['allowDefault'])?(!$config['allowDefault']?DISALLOW_USER:""):null;
    }

    public function getAdminControllers()
    {
        return 'GLFramework\Modules\GroupPermissions\groups';
    }

    public static function getGroupsByUser($user) {
        $user_id = $user->id;
        $group = new \Group();
        if(self::$groupsUserCache[$user_id]) {
            return self::$groupsUserCache[$user_id];
        }
        self::$groupsUserCache[$user_id] = $group->getByUser($user);
        return self::$groupsUserCache[$user_id];

    }

    public static function isGroupPages($id_group, $id_page) {
        if(!self::$cache) {
            $groupPages = new \GroupPage();
            self::$cache = $groupPages->get_all()->getModels();
        }
        foreach (self::$cache as $key => $value) {
            if($value->id_group == $id_group && $value->id_page == $id_page) {
                return true;
            }
        }
        return false;
        
    }
}