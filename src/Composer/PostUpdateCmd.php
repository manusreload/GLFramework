<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 23/5/17
 * Time: 13:21
 */

namespace GLFramework\Composer;

use Composer\Script\Event;

/**
 * Class PostUpdateCmd
 *
 * @package GLFramework\Composer
 */
class PostUpdateCmd
{
    /**
     * TODO
     *
     * @param Event $event
     */
    public static function exec(Event $event)
    {

    }

    /**
     * TODO
     *
     * @param Event $event
     */
    public static function postAutoloadDump(Event $event)
    {
        echo "Ok\n";
    }
}
