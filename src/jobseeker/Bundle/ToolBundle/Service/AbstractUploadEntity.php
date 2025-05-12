<?php

namespace jobseeker\Bundle\ToolBundle\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use jobseeker\Bundle\ToolBundle\Service\ImageMagick;

abstract class AbstractUploadEntity
{

    protected static $allowedExtensions = array("jpeg", "png", "bmp", "doc", "docx", "pdf");
    protected static $max_size = 5242880;
    protected static $error = array("ok" => 0, "error_type" => 1, "error_size" => 2, "error_other" => 3);
    protected $file;

    protected function setFile($file = array())
    {
        $this->file = $file;
    }

    protected function getFile($key = null)
    {
        $file = $this->file;
        return null === $key ? $file : isset($file[$key]) ? $file[$key] : null;
    }

    public function upload($controller, $name, $targetDirectory)
    {
        $getName = "get" . ucfirst($name);
        $object = $this->$getName();
        if (null === $object || !$object instanceof UploadedFile) {
            return;
        }

        $status = $this->preUpload($controller, $object);

        if ($status > 0) {
            return $status;
        }

        try {
            $object->move($targetDirectory . "/" . $this->getFile("path"), $this->getFile("name") . "." . $this->getFile("ext"));
            if (strtolower($name) == "avator") {
                $imagemagick = new ImageMagick(
                    $targetDirectory . "/" . $this->getFile("path") . $this->getFile("name") . "." . $this->getFile("ext"), $targetDirectory . "/" . $this->getFile("path") . "1x_" . $this->getFile("name") . "." . $this->getFile("ext")
                );
                $imagemagick->setImageQuality(100)->resizeExactly(60, 60);

                $imagemagick2 = new ImageMagick(
                    $targetDirectory . "/" . $this->getFile("path") . $this->getFile("name") . "." . $this->getFile("ext"), $targetDirectory . "/" . $this->getFile("path") . "2x_" . $this->getFile("name") . "." . $this->getFile("ext")
                );
                $imagemagick2->setImageQuality(100)->resizeExactly(100, 100);
            }
        } catch (\Exception $e) {
            return self::$error['error_other'];
        }

        $this->afterUpload($name);

        return self::$error['ok'];
    }

    protected function preUpload($controller, $object)
    {
        $size = $object->getClientSize();
        if ($size <= 0 || $this->toBytes(ini_get('post_max_size')) < $size || $this->toBytes(ini_get('upload_max_filesize')) < $size || self::$max_size < $size) {
            return self::$error['error_size'];
        }

        $guessExtension = $object->guessClientExtension();
        if (!in_array($guessExtension, self::$allowedExtensions)) {
            return self::$error['error_type'];
        }

        if ($object->getError() > 0) {
            return self::$error['error_other'];
        }

        $originalName = $object->getClientOriginalName();
        $extension = $object->getClientOriginalExtension();
        $encodedName = $controller->encodeUploadFileName($originalName);
        $this->setFile(array(
            "path" => $encodedName['path'],
            "name" => $encodedName["name"],
            "ext" => $extension
        ));

        return self::$error['ok'];
    }

    protected function afterUpload($name)
    {
        $name = "set" . ucfirst($name);
        $this->$name($this->getFile("path") . $this->getFile("name") . "." . $this->getFile("ext"));
        $this->setFile();
    }

    public function getView($controller, $name)
    {
        $getName = "get" . ucfirst($name);
        $rootPath = $getName . "RootPath";
        $object = $this->$getName();
        $file = $controller->decodeUploadFileName($object);

        $fullFile = $controller->$rootPath() . "/" . $file['path'] . $file['name'];

        $file_exist = is_file($fullFile) && file_exists($fullFile);
        if (!$file_exist) {
            $fullFile = $controller->$rootPath() . "/avator.png";
        }

        $originFile = $file['name'];

        return new UploadedFile($fullFile, $originFile);
    }

    protected function toBytes($str)
    {
        $last = strtolower($str[strlen($str) - 1]);
        $val = trim(strtolower($str), $last);
        switch ($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
        return $val;
    }

}
