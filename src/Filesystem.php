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
 * Date: 29/03/16
 * Time: 12:00
 */

namespace GLFramework;


use TijsVerkoyen\CssToInlineStyles\Exception;

class Filesystem
{

    private $file;
    private $folder;
    private $pfile;

    /**
     * Generara un gestor de archivos
     * @param $file
     * @param null $folder
     */
    public function __construct($file = null, $folder = null)
    {
        if(!$folder) $folder = $this->getFilesystemFolder();
        $this->folder = $folder;
        $this->file = $file;
    }

    /**
     * Obtiene la ruta al directorio donde se almacenan los archivos
     * @return string
     */
    public function getFilesystemFolder()
    {

        $config = Bootstrap::getSingleton()->getConfig();
        if(isset($config['app']['filesystem']))
        {
            return $config['app']['filesystem'];
        }
        return "filesystem";
    }

    /**
     * Solicita un nuevo archivo con ese nombre y extension, si no se indica un nombre, se genera
     * uno de forma aleatoria.
     * @param null $filename
     * @param string $extension
     * @param null $folder
     * @return Filesystem
     */
    public static function allocate($filename = null, $extension = ".rnd", $folder = null)
    {
        if($filename == null)
            $filename = sha1(time() . "_" . microtime(true));
        $file = new Filesystem("{$folder}/{$filename}{$extension}"); //$this->getStorage() . "/{$filename}{$extension}";
        return $file;
    }

    private function getStorage()
    {
        $folder = $this->folder;
        if(!is_dir($folder))
        {
            if(!mkdir($folder, 0777, true))
            {
                throw new Exception("Can not create Filesystem folder: '" . $folder . "'. Please verify permissions.");
            }
        }

        return $folder;
    }


    /**
     * Obtiene la ruta relativa al archivo
     * @return string
     * @throws Exception
     */
    public function getFilePath()
    {
        return $this->getStorage() . "/" . $this->file;
    }

    /**
     * Obtiene la rut absoluta al archivo
     * @return string
     * @throws Exception
     */
    public function getAbsolutePath()
    {
        return realpath($this->getStorage()) . "/" . $this->file;
    }

    /**
     * Crea el archivo vacio
     * @return bool
     */
    public function touch()
    {
        return touch($this->getAbsolutePath());
    }

    /**
     * Devuelve true si existe el archivo
     * @return bool
     */
    public function exists()
    {
        return file_exists($this->getAbsolutePath());
    }

    /**
     * Obtiene una url accesible por el navegador
     * @return string
     */
    public function url()
    {
        $scheme = "http://";
        if($_SERVER['HTTPS']) $scheme = "https://";
        return $scheme . $_SERVER['HTTP_HOST'] . "/" . $this->getFilePath();
    }

    /**
     * Abrir un archivo, devuelve el puntero al archio
     * @param string $mode
     * @return resource
     */
    public function open($mode = "rw")
    {
        return ($this->pfile = fopen($this->getAbsolutePath(), $mode));
    }

    /**
     * Cierra el último puntero abiero
     * @return bool
     */
    public function close()
    {
        return fclose($this->pfile);
    }

    /**
     * Leer el contenido del archivo. No se requiere ejecutar open() ni close()
     * @param bool $output Enviar contenido al navegador
     * @return string
     */
    public function read($output = false)
    {
        if($output)
        {
            $handle = $this->open();
            if ($handle) {
                while (!feof($handle)) {
                    echo fgets($handle, 4096);
                    // Process buffer here..
                }
                fclose($handle);
            }
        }
        else
        {
            return file_get_contents($this->getAbsolutePath());
        }
    }

    /**
     * Escribir el contenido al archivo. No se requiere ejecutar open() ni close()
     * @param $content
     * @return int
     */
    public function write($content)
    {
        return file_put_contents($this->getAbsolutePath(), $content);
    }

    public function getSize()
    {
        return filesize($this->getAbsolutePath());
    }

    /**
     * Considera que este archivo es una carpeta
     */
    public function mkdir()
    {
        mkdir($this->getAbsolutePath(), 0777, true);
    }

    /**
     * @return null
     */
    public function getFile()
    {
        return $this->file;
    }

    function __toString()
    {
        return $this->getAbsolutePath();
    }


}