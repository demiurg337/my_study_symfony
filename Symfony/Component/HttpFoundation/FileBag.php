<?php

namespace Symfony\Component\HttpFoundation;
require_once __DIR__.'/File/UploadedFile.php';
require_once __DIR__.'/ParameterBag.php';
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileBag extends ParameterBag
{
    /*
    Массив для исправления структуры массива
    */
    private static $fileKeys = array('error', 'name', 'size', 'tmp_name', 'type');


    public function __construct(array $p = array()) 
    {
        $this->replace($p);
    }

    /*
    Эсть какойто баг с массивом $_FILES
    что ломаная структура
    */
    protected function fixPhpFilesArray($data)
    {
        if (!is_array($data)) {
            return $data;
        }

        $keys = array_keys($data);
        sort($keys);

        if (self::$fileKeys != $keys || !isset($data['name']) || !is_array($data['name'])) {
            return $data;
        }

        $files = $data;
        foreach (self::$fileKeys as $k) {
            unset($files[$k]);
        }

        foreach (array_keys($data['name']) as $key) {
            $files[$key] = $this->fixPhpFilesArray(array(
                'error'    => $data['error'][$key],
                'name'     => $data['name'][$key],
                'type'     => $data['type'][$key],
                'tmp_name' => $data['tmp_name'][$key],
                'size'     => $data['size'][$key]
            ));
        }

        return $files;
    }

    public function convertFileInformation($file)
    {
        if ($file instanceof UploadedFile) {
            return $file;
        }
        $file = $this->fixPhpFilesArray($file);

        if (is_array($file)) {
            $keys = array_keys($file);
            sort($keys);
            
            if ($keys == self::$fileKeys) {
                if (UPLOAD_ERR_NO_FILE == $file['error']) {
                    $file = null;
                }
                else {
                    $file = new UploadedFile($file['tmp_name'], $file['name'], $file['type'], $file['size'], $file['error']);
                }
            }
            else {
                /*
                Метод вызывает себя же
                По сути здесь будет  рекурсия
                 */
                
                $file = array_map(array($this, 'convertFileInformation'), $file);
            }    
        }

        return $file;
    }

    public function set($key, $val)
    {
        if (!is_array($val) && !$val instanceof UploadedFile) {
            throw new \InvalidArgumentException('Some textt');
        }

        parent::set($key, $this->convertFileInformation($val));
    }

    public function add(array $files = array())
    {
        foreach($files as $key => $val) {
            $this->set($key, $val);
        }
    }

    public function replace(array $f = array())
    {
        $this->parameters = array();
        $this->add($f);
    }
}

