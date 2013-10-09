<?php
namespace Symfony\Component\HttpFoundation\File\MimeType;
require_once __DIR__.'/MimeTypeGuesserInterface.php';
/*
Етот файл использует PHP разшырение FileInfo
*/
class FileinfoMimeTypeGuesser implements  MimeTypeGuesserInterface
{
    /*
    Файл с информауие про то как определяти информацию о файле
    */
    private $dataBaseFile;

    public function __construct($file = null)
    {
        $this->dataBaseFile = $file;
    }

    public function isSupported()
    {
        return function_exists('finfo_open');
    }


    public function guess($path)
    {
        
        if (!is_file($path)) {
            throw new FileNotFoundException($path);
        }

        if (!is_readable($path)) {
            throw new FileNotFoundException($path);
        }

        if (!self::isSupported()) {
            return null;
        }

        if (! $info = new \finfo(FILEINFO_MIME_TYPE, $this->dataBaseFile)) {
            return null;    
        }

        return $info->file($path);    
    }
}
