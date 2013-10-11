<?php

namespace Symfony\Component\HttpFoundation\File;

require_once __DIR__.'/File.php';
require_once __DIR__.'/Exception/FileException.php';
require_once __DIR__.'/Exception/FileNotFoundException.php';
require_once __DIR__.'/MimeType/ExtensionGuesser.php';
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;
class UploadedFile extends File
{

    private 
        /*
        Переменная которая опредяляет в каком 
        режыме запущен файл

        Если в режыме тестирования то пропускауются
        некоторые проверки

        !!!
        переменная спецыально для прохождения тестов
        !!!
        */
        $test = false,
        $originalName,
        $size,
        $mimeType;


    /*
    $path = Времеенное название файла во время загрузки.
    $originalName = оригинальное название файла( тоесть как он загружался ???)
    */
    public function __construct($path, $originalName, $mimeType = null, $size = null, $error = null, $test = false) {
        /*
        Проверка конфигурации пхп можно ли загружаить файлы
        get_cfg_var('cfg_file_path') - берет где находится файл конфигурация
        также с помощью этой конструкции можно проверять используеь ли пхп файл конфигурации
        */
        if (!ini_get('file_uploads')) {
            throw new FileException('Cant upload files:'. get_cfg_var('cfg_file_path'));    
        }


        $this->mimeType = $mimeType ?: 'application/octet-stream';
        $this->originalName = $this->getName( $originalName);
        $this->size = $size;
        $this->error = $error ?: UPLOAD_ERR_OK; //UPLOAD_ERR_OK - значит все загружено
        $this->test = (Boolean) $test;
        /*
        Когда заружена картинка ще проверка сохранен ли файл
        UPLOAD_ERR_OK === $this->error
        */
        parent::__construct($path, UPLOAD_ERR_OK === $this->error);
    }


    public function getClientOriginalName()
    {
        return $this->originalName;
    }

    public function getClientOriginalExtension()
    {
        return pathinfo($this->originalName, PATHINFO_EXTENSION);
    }

    public function getClientMimeType()
    {
        return $this->mimeType;
    }

    public function guessClientExtension()
    {
        $guesser = ExtensionGuesser::getInstance();
        return $guesser->guess($this->getClientMimeType());  
    } 

    public function getClientSize()
    {
        return $this->size;    
    }

    public function getError()
    {
        return $this->error;
    }

    public function isValid()
    {
        $isNorm = $this->error === UPLOAD_ERR_OK;

        return $this->test ? $isNorm : $isNorm && is_uploaded_file($this->getPathname());
    }

    public function move($dir, $fileName = null) 
    {
        if ($this->isValid()) {
            if ($this->test) {
                parent::move($dir, $fileName);
            }           
            
            $target = $this->getTargetFile($dir, $fileName);


            if (!@move_uploaded_file($this->getPathname(), $target)) {
                $err = error_get_last();
                throw new FileException('Error move uploaded file:'.strip_tags($err['message']));
            }

            @chmod($target, 0666 & ~umask());

            return $target;
        }


    }

    public static function getMaxFilesize() 
    {
        $maxSize = strtolower(ini_get('upload_max_filesize'));

        /*
        Если максимальный объем файла не
        задан в настроках
        */
        if ($maxSize === '') {
            /*
            Если не указано максимальный объем
            то вроде тогда маскимально возможным объемом для загрузки щитается
            максимально большое число что допустимое число
            */
            return PHP_INT_MAX;    
        }

        
        $maxSize = ltrim($maxSize, '+'); //может иметть сначала плюч, от чего?
        /*
        Объем может быть указан 
        в разных системах исчеслений
        */

        
        if (0 ===  strpos($maxSize, '0x')) {
            $maxSize = intval($maxSize, 16);
        }
        elseif (0 == strpos($maxSize, '0')) {
            $maxSize = intval($maxSize, 8);
                
        }
        else {
            $maxSize = intval($maxSize, 10);    
        }

        /*
        !!!
        Интересный способ перевода размера
        !!!

        Здесь нет break, тоесть просто указывается
        с какого пункта нужно умножать
        */
        switch(substr($maxSize, -1)) {
            case 't': $maxSize *= 1024;
            case 'g': $maxSize *= 1024;
            case 'm': $maxSize *= 1024;
            case 'k': $maxSize *= 1024;
        }

        return $maxSize;
    }

    public function getErrorMessage($errCode)
    {

        /*
         Для чего здесь статическая переменная
         могу только догадыватья

         но

        */
        static $errors = array(
            UPLOAD_ERR_INI_SIZE   => 'The file "%s" exceeds your upload_max_filesize ini directive (limit is %d kb).',
            UPLOAD_ERR_FORM_SIZE  => 'The file "%s" exceeds the upload limit defined in your form.',
            UPLOAD_ERR_PARTIAL    => 'The file "%s" was only partially uploaded.',
            UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
            UPLOAD_ERR_CANT_WRITE => 'The file "%s" could not be written on disk.',
            UPLOAD_ERR_NO_TMP_DIR => 'File could not be uploaded: missing temporary directory.',
            UPLOAD_ERR_EXTENSION  => 'File upload was stopped by a php extension.',
            );

        $maxFileSize = $errCode === UPLOAD_ERR_INI_SIZE ? self::getMaxFilesize() / 1024 : 0;
        $mes = isset($errors[$errCode]) ? $errors : 'Some new error file %s';

        return sprintf($mes, $this->getClientOriginalName(), $maxFileSize);

    }

}
