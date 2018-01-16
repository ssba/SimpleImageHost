<?php


namespace Core;

class GD2
{
    static private $instance = null;

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __clone()
    {
    }

    private function __construct()
    {
    }

    public static function stampQR(string $image,string $stamp) : string
    {
        return GD2::getInstance()->_stampQR($image, $stamp);
    }

    private function _stampQR(string $imagePath,string $stamp) : string
    {
        $qrPath = IMG_PATH . time() . microtime() . 'qr0.png';
        \QRcode::png($stamp, $qrPath, QR_ECLEVEL_L, 3, 0);

        $imageResourse = $this->_readImageByType($imagePath);
        $qrResourse = $this->_readImageByType($qrPath);

        $marge_right = 10;
        $marge_bottom = 10;
        $sx = imagesx($qrResourse);
        $sy = imagesy($qrResourse);

        imagecopy($imageResourse, $qrResourse, imagesx($imageResourse) - $sx - $marge_right, imagesy($imageResourse) - $sy - $marge_bottom, 0, 0, imagesx($qrResourse), imagesy($qrResourse));

        $this->_saveImageByType($imageResourse, $imagePath);
        imagedestroy($imageResourse);
        imagedestroy($qrResourse);

        unlink($qrPath);
        return $imagePath;
    }

    private function _readImageByType(string $image) {

        switch (exif_imagetype($image)){
            case (IMAGETYPE_GIF):
                return imagecreatefromgif($image);
                break;
            case (IMAGETYPE_JPEG):
                return imagecreatefromjpeg ($image);
                break;
            case (IMAGETYPE_PNG):
                return imagecreatefrompng($image);
                break;
            default:
                return null;
                break;
        }
        return null;
    }

    /**
     * Save stampd image as temp file with old hash
     * @param $image
     * @param $name
     * @param $type
     * @return bool|null
     */
    private function _saveImageByType($image, $path) {

        switch (exif_imagetype($path)){
            case (IMAGETYPE_GIF):
                return imagegif($image, $path);
                break;
            case (IMAGETYPE_JPEG):
                return imagejpeg($image, $path);
                break;
            case (IMAGETYPE_PNG):
                return imagepng($image, $path);
                break;
            default:
                return null;
                break;
        }
        return null;
    }

}
