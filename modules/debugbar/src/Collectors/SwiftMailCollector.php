<?php
/**
 * Created by PhpStorm.
 * User: mmunoz
 * Date: 8/06/17
 * Time: 10:39
 */

namespace GLFramework\Modules\Debugbar\Collectors;


class SwiftMailCollector extends \DebugBar\Bridge\SwiftMailer\SwiftMailCollector
{

    public function collect()
    {
        $mails = array();
        foreach ($this->messagesLogger->getMessages() as $msg) {
            $mails[] = array(
                'to' => $this->formatTo($msg->getTo()),
                'subject' => $msg->getSubject(),
                'headers' => $msg->getHeaders()->toString()
            );
        }
        return array(
            'count' => count($mails),
            'mails' => $mails
        );
    }
}