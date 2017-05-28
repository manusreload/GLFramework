<?php

namespace GLFramework\FTP;

/**
 * Created by PhpStorm.
 * User: mmuno
 * Date: 29/06/2016
 * Time: 11:02
 */

/**
 * Class FTPConnection
 *
 * @package GLFramework\FTP
 */
class FTPConnection
{
    private $host;
    private $username;
    private $password;
    private $chdir;
    private $chmod = null;
    private $passive = false;
    private $exclude = array();

    private $connection;
    private $connected;

    private $transferences = array();

    /**
     * FTPConnection constructor.
     *
     * @param null $host
     * @param null $username
     * @param null $password
     * @param null $chdir
     */
    public function __construct($host = null, $username = null, $password = null, $chdir = null)
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->chdir = $chdir;

        if (file_exists('.gitignore')) {
            $this->addIgnore('.gitignore');
        }

        print_debug($this->exclude);
    }

    /**
     * TODO
     *
     * @return bool
     */
    public function connect()
    {
        $this->connection = ftp_connect($this->host);
        if (ftp_login($this->connection, $this->username, $this->password)) {
            if (ftp_chdir($this->connection, $this->getChdir()) && ftp_pasv($this->connection, $this->passive)) {
                $this->connected = true;
                return true;
            }
        }
        return false;
    }

    /**
     * TODO
     *
     * @param $file
     * @return int
     */
    public function getTransferenceEncoding($file)
    {
        return FTP_BINARY;
    }

    /**
     * TODO
     *
     * @param $remote
     * @param $local
     * @return bool
     */
    public function getFile($remote, $local)
    {
        return ftp_get($this->connection, $local, $remote, $this->getTransferenceEncoding($remote));
    }

    /**
     * TODO
     *
     * @param $local
     * @param $remote
     * @return bool
     */
    public function putFile($local, $remote)
    {
        return ftp_put($this->connection, $remote, $local, $this->getTransferenceEncoding($local));
    }

    /**
     * TODO
     *
     * @param $remote
     * @return bool
     */
    public function deleteFile($remote)
    {
        return ftp_delete($this->connection, $remote);
    }

    /**
     * TODO
     *
     * @param $command
     * @return bool
     */
    public function exec($command)
    {
        return ftp_exec($this->connection, $command);
    }

    /**
     * TODO
     *
     * @param $directory
     * @return string
     */
    public function mkdir($directory)
    {
        return ftp_mkdir($this->connection, $directory);
    }

    /**
     * TODO
     *
     * @param $directory
     * @return array
     */
    public function listDirectory($directory)
    {
        return ftp_nlist($this->connection, $directory);
    }

    /**
     * TODO
     *
     * @param $localDirectory
     * @param array $result
     * @param string $parent
     * @return array
     */
    public function getSnapshot($localDirectory, &$result = array(), $parent = '')
    {
        $files = scandir($localDirectory);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $name = $parent . $file;
                if (!$this->isExcluded($name)) {
                    $filename = $localDirectory . '/' . $file;

                    if (is_dir($filename)) {
                        $this->getSnapshot($filename, $result, $name . '/');
                    } else {
                        $result[$name] = sha1($filename);
                    }
                }
            }
        }
        return $result;
    }

    /**
     * TODO
     *
     * @param $file
     */
    public function addIgnore($file)
    {
        $dir = realpath(dirname($file));
        $data = explode("\n", file_get_contents($file));
        foreach ($data as $line) {
            if ($line === '') {
                continue;
            }
            if (substr($line, 0, 1) === '#') {
                continue;
            }   # a comment
            if (substr($line, 0, 1) === '!') {           # negated glob
                $line = substr($line, 1);
                $files = array_diff(glob("$dir" . DIRECTORY_SEPARATOR . "*"),
                    glob("$dir" . DIRECTORY_SEPARATOR . "$line"));
            } else {
                $files = glob($dir . DIRECTORY_SEPARATOR . $line);
            }
            print_r($files);
            $this->exclude = array_merge($this->exclude, $files);
        }
        print_debug($this->exclude, $dir . DIRECTORY_SEPARATOR . $line);
    }

    /**
     * TODO
     *
     * @param $file
     */
    public function parseSnapshot($file)
    {
        $data = file_get_contents($file);
        $lines = explode("\n");
    }

    /**
     * TODO
     *
     * @return mixed
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * TODO
     *
     * @param mixed $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * TODO
     *
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * TODO
     *
     * @param mixed $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * TODO
     *
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * TODO
     *
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * TODO
     *
     * @return mixed
     */
    public function getChdir()
    {
        return $this->chdir;
    }

    /**
     * TODO
     *
     * @param mixed $chdir
     */
    public function setChdir($chdir)
    {
        $this->chdir = $chdir;
    }

    /**
     * TODO
     *
     * @return int
     */
    public function getChmod()
    {
        return $this->chmod;
    }

    /**
     * TODO
     *
     * @param int $chmod
     */
    public function setChmod($chmod)
    {
        $this->chmod = $chmod;
    }

    /**
     * TODO
     *
     * @return boolean
     */
    public function isPassive()
    {
        return $this->passive;
    }

    /**
     * TODO
     *
     * @param boolean $passive
     */
    public function setPassive($passive)
    {
        $this->passive = $passive;
    }

    /**
     * TODO
     *
     * @param $name
     * @return bool
     */
    private function isExcluded($name)
    {
        foreach ($this->exclude as $item) {
            if (fnmatch($item, $name)) {
                return true;
            }
        }
        return false;
    }
}
