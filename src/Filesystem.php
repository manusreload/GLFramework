<?php
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

    private $pfile;
    /**
     * Generara un gestor de archivos
     * @param $file
     */
    public function __construct($file = null)
    {
        $this->file = $file;
    }

    /**
     * Solicita un nuevo archivo con ese nombre y extension, si no se indica un nombre, se genera
     * uno de forma aleatoria.
     * @param null $filename
     * @param string $extension
     * @return Filesystem
     */
    public function allocate($filename = null, $extension = ".rnd")
    {
        if($filename == null)
            $filename = sha1(time() . "_" . microtime(true));
        $file = new Filesystem("{$filename}{$extension}"); //$this->getStorage() . "/{$filename}{$extension}";
        $file->touch();
        return $file;
    }

    private function getStorage()
    {
        $folder = $this->getFilesystemFolder();
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
     * @return string
     */
    public function read()
    {
        return file_get_contents($this->getAbsolutePath());
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

}