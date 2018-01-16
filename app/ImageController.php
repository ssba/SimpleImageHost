<?php

namespace App;

use Core\LayoutProcessor;
use Core\Storage;
use Core\GD2;

class ImageController
{

    public function __construct()
    {
    }

    public function upload()
    {
        try {

            if ($_POST['csrf_token'] == $_SESSION['csrf_token']) {
                unset($_SESSION['csrf_token']);
            } else {
                throw new \Exception("Server Error", 500);
            }

            $fileID = $this->guidv4();
            $fileHash = $this->hashWithSalt($_FILES['image']['tmp_name']);
            $filePathInfo = pathinfo($_FILES['image']['name']);
            $uploadedFilePath = IMG_PATH . $fileHash . "." . $filePathInfo['extension'];

            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadedFilePath)) {
                if (!empty($_POST['wm'])) {
                    GD2::stampQR($uploadedFilePath, $_POST['wm']);
                    $fileHash = $this->hashWithSalt($uploadedFilePath);
                    $newFilePath = IMG_PATH . $fileHash . "." . $filePathInfo['extension'];
                    if (!rename($uploadedFilePath, $newFilePath))
                        throw new \Exception("Server Error", 500);
                    $uploadedFilePath = $newFilePath;
                }

                $_time = time() + 3600;
                Storage::record($fileID, $uploadedFilePath, $_time);

                $imageData = base64_encode(file_get_contents($uploadedFilePath));
                $src = 'data: ' . mime_content_type($uploadedFilePath) . ';base64,' . $imageData;

                return LayoutProcessor::getTPL('MainTPL', 'upload', ['img' => $src, 'time' => date("Y-m-d H:i:s", $_time), 'url' => $_SERVER['HTTP_ORIGIN'] . '/image/' . $fileID]);
            }

            return false;
        } catch (\Exception $e) {
            //TODO Write Error data in log provider
            header('HTTP/1.1 500 Internal Server Error');
        }
    }

    public function getImage($id, array $data)
    {
        $img = Storage::get($id);
        $filePathInfo = pathinfo($img['path']);
        if (!$this->searchFile($filePathInfo['filename']))
            throw new \Exception('File not found', 404);

        $imageData = base64_encode(file_get_contents($img['path']));
        $src = 'data: ' . mime_content_type($img['path']) . ';base64,' . $imageData;

        unlink($img['path']);
        Storage::unlink($id);

        return LayoutProcessor::getTPL('MainTPL', 'image', ['img' => $src]);

    }

    public function clearObsolete()
    {
        $imgs = Storage::obsolete(time(), true);
        foreach ($imgs as $img){
            @unlink($img['path']);
        }

        header("HTTP/1.1 204 NO CONTENT");
    }

    private function hashWithSalt(string $filePath, string $salt = null): string
    {

        if (is_null($salt))
            $salt = bin2hex(openssl_random_pseudo_bytes(12));

        $file_hash = hash_file('sha256', $filePath);
        return hash('sha256', $file_hash . $salt);

    }

    private function searchFile(string $fileName): string
    {
        $uploadDir = opendir(IMG_PATH);

        while (false !== ($file = readdir($uploadDir))) {
            if (preg_match('/^' . $fileName . '.*$/i', $file)) {
                return IMG_PATH . $file;
            }
        }
        return null;
    }

    private function guidv4(): string
    {
        if (function_exists('com_create_guid') === true) {
            $_guid = str_replace("-", "", com_create_guid());
            return trim(strtolower($_guid), '{}');
        }

        $data = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
        return vsprintf('%s%s%s%s%s%s%s%s', str_split(bin2hex($data), 4));
    }

}