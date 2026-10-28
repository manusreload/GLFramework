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
 * Date: 1/03/16
 * Time: 10:33
 */

namespace GLFramework\DaMa\Manipulators;

/**
 * Class XLSXManipulator
 *
 * Lee ficheros Excel 2007+ (.xlsx) con PhpSpreadsheet, reutilizando XLSManipulator.
 *
 * @package GLFramework\DaMa\Manipulators
 */
class XLSXManipulator extends XLSManipulator
{
    protected $readerType = 'Xlsx';
}
