<?php
/**
 *     GLFramework, small web application framework.
 *     Copyright (C) 2016.  Manuel Muñoz Rosa
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation, either version 3 of the License, or
 *     (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU General Public License for more details.
 *
 *     You should have received a copy of the GNU General Public License
 *     along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Created by PhpStorm.
 * User: manus
 * Date: 10/5/16
 * Time: 11:06
 */

namespace GLFramework\Mail;

/**
 * Class Mail
 *
 * @package GLFramework\Mail
 */
class Mail extends MailSystem
{
    /**
     * TODO
     *
     * @return \Swift_MailTransport
     */
    public function getTransport()
    {
        // TODO: Implement getTransport() method.
        return new \Swift_MailTransport();
    }
}
