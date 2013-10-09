<?php

namespace Symfony\Component\HttpFoundation\File;

require_once __DIR__.'/Exception/FileException.php';
require_once __DIR__.'/Exception/FileNotFoundException.php';
require_once __DIR__.'/MimeType/MimeTypeExtensionGuesser.php';
require_once __DIR__.'/MimeType/ExtensionGuesser.php';
require_once __DIR__.'/MimeType/MimeTypeGuesser.php';
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeExtensionGuesser;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;
class File extends \SplFileInfo
{
    public function __construct($path, $checkPath = true)
    {
        if ($checkPath && !is_file($path)) {
            throw new Exception\FileNotFoundException($path);
        }

        parent::__construct($path);

    }

    public function getMimeType()
    {
        $inst = MimeTypeGuesser::getInstance();
        return $inst->guess($this->getPathname());
    }

    public function guessExtension()
    {
        $mimeType = $this->getMimeType();
        $extGuesser = ExtensionGuesser::getInstance();
        return $extGuesser->guess($mimeType);
    }

    public function getExtension()
    {
        return pathInfo($this->getBasename(), PATHINFO_EXTENSION);
    }

    /*
    Вроде Использутся для перейменувания файла
    Поскоьку здесь только создается папки
    куда нужно переместить файл
    */
    protected function getTargetFile($dir, $name = null) {
        if (!is_dir($dir)) {
            if (false === @mkdir($dir, 0777, true)) {
                throw new FileException('Cant create dir');
            }
        }
        elseif (!is_writable($dir)) {
            throw new FileException('Not writable');
        }

        /*
        Новый адрес файла
        */
        $target = rtrim($dir, '/\\').DIRECTORY_SEPARATOR.(null === $name ? $this->getBasename : $this->getName($name));


        /*
        Создает сам себя
        Создает новый файл информации для
        еще НЕ созданого
        */
        return new File($target, false);
    }


    protected function getName($name)
    {
        $originalName = str_replace('\\', '/', $name);
        $pos = strrpos($originalName, '/');
        
        return ($pos === false ? $originalName : substr($originalName, $pos + 1));
    }

    public function move($dir, $file = null)
    {
        /*
        Генерация нового адреса и если нужно создание 
        папок
        И по сути создание ного файла информации
        */
        $target = $this->getTargetFile($dir, $file);
        
        /*
        $target - хоть и не строка а объект файла в котором 
        находитс путь-азвание файла.
        РПРи импользованри етого параметра здесь, вроди бы осуществляется
        возвращяется название файла
        */
        if (!@rename($this->getPathname(), $target)) {
            $err = error_get_last();
            throw new FileException('Cant rename:  '.strip_tags($err['message']));
        }

        /*
        Пока не понятно почему имено такая
        побайтовая операция
        */
        @chmod($target, 0666 & ~umask());

        return $target;
    }
}
