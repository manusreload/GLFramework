<?php
/**
 * Created by PhpStorm.
 * User: mmunoz
 * Date: 11/01/17
 * Time: 17:16
 */

namespace GLFramework\Image;

/**
 * Class ImageProcessing
 *
 * @package GLFramework\Image
 */
class ImageProcessing
{
    private $file;
    private $image;

    /**
     * ImageProcessing constructor.
     *
     * @param $file
     */
    public function __construct($file)
    {
        $this->file = $file;
    }

    /**
     * TODO
     *
     * @param string $type
     * @return null|resource
     */
    public function open($type = 'image/jpeg')
    {
        switch ($type) {
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

    /**
     * TODO
     *
     * @param $w
     * @param $h
     * @param bool $crop
     */
    public function resize_image($w, $h, $crop = false)
    {
        list($width, $height) = getimagesize($this->file);
        $r = $width / $height;
        if ($crop) {
            if ($width > $height) {
                $width = ceil($width - ($width * abs($r - $w / $h)));
            } else {
                $height = ceil($height - ($height * abs($r - $w / $h)));
            }
            $newwidth = $w;
            $newheight = $h;
        } else {
            if ($w / $h > $r) {
                $newwidth = $h * $r;
                $newheight = $h;
            } else {
                $newheight = $w / $r;
                $newwidth = $w;
            }
        }
        $dst = imagecreatetruecolor($newwidth, $newheight);
        imagecopyresampled($dst, $this->image, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
        $this->image = $dst;
    }

    /**
     * TODO
     *
     * @param $file
     * @return bool
     */
    public function save($file)
    {
        return imagepng($this->image, $file);
    }
}
