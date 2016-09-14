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
 * Date: 13/1/16
 * Time: 19:38
 */

namespace GLFramework;


class Versions
{
    private $filename = "versions.json";
    public function getVersions()
    {
        return json_decode(file_get_contents($this->filename));
    }

    public function getLastVersion()
    {
        $versions = $this->getVersions();
        return $versions[0];
    }

    public function getVersion($index = 0)
    {
        $versions = $this->getVersions();
        return $versions[$index];
    }

}