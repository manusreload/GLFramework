<?php
/**
 * Created by PhpStorm.
 * User: mmuno
 * Date: 29/06/2016
 * Time: 11:00
 */

require "vendor/autoload.php";

$ftp = new \GLFramework\FTP\FTPConnection();
$config = json_decode(file_get_contents($argv[1]));
$ftp->setUsername($config->username);
$ftp->setHost($config->host);
$ftp->setPassword($config->password);
$ftp->setChdir($config->chdir);

if($ftp->connect())
{
    echo "Connection OK\n";
    echo "Generating file snapshot...\n";
    $snapshot = $ftp->getSnapshot(".");
    echo "Count total: " . count($snapshot) . " files scanned!\n";

    if($argv[2] == "push")
    {
        $tmp = tempnam("/tmp", "ftp");
        if($ftp->getFile("snapshot.sha1", $tmp))
        {
            
        }
        else
        {
            echo "'snapshot.sha1' not found in remote, use the init command to initialize!\n";
        }
    }
    
}
else
{
    echo "Error connecting to the FTP server!\n";
}

