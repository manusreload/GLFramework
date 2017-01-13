<?php
/**
 * Created by PhpStorm.
 * User: mmunoz
 * Date: 11/01/17
 * Time: 17:16
 */

namespace GLFramework\Image;


class ImageProcessing
{

    private $file;
    private $image;

    /**
     * ImageProcessing constructor.
     * @param $file
     */
    public function __construct($file)
    {
        $this->file = $file;
    }

    public function open($type = "image/jpeg")
    {
        switch ($type)
        {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($this->file);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($this->file);
                break;
            case 'image/png':
                $image = imagecreatefrompng($this->file);
                break;
            default:
                $image = null;
        }
        return $this->image = $image;
    }


    public function resize_image($w, $h, $crop=FALSE) {
        list($width, $height) = getimagesize($this->file);
        $r = $width / $height;
        if ($crop) {
            if ($width > $height) {
                $width = ceil($width-($width*abs($r-$w/$h)));
            } else {
                $height = ceil($height-($height*abs($r-$w/$h)));
            }
            $newwidth = $w;
            $newheight = $h;
        } else {
            if ($w/$h > $r) {
                $newwidth = $h*$r;
                $newheight = $h;
            } else {
                $newheight = $w/$r;
                $newwidth = $w;
            }
        }
        $dst = imagecreatetruecolor($newwidth, $newheight);
        imagecopyresampled($dst, $this->image, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
        $this->image = $dst;
    }

    public function save($file)
    {
        return imagepng($this->image, $file);
    }


}