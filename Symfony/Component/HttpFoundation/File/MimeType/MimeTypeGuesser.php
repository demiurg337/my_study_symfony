<?php
namespace Symfony\Component\HttpFoundation\File\MimeType;
require_once __DIR__.'/../Exception/FileNotFoundException.php';
require_once __DIR__.'/MimeTypeGuesserInterface.php';
require_once __DIR__.'/FileBinaryMimeTypeGuesser.php';
require_once __DIR__.'/FileinfoMimeTypeGuesser.php';
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

//Singlton

class MimeTypeGuesser implements MimeTypeGuesserInterface
{
    private static $instance = null;    
    protected $guessers = array();

    /*
    Серце синглтона
    */
    public static function getInstance()
    {
        if (null === self::$instance) {
            /*
            Позднее статическое связывание
            */
            self::$instance = new self();
        }

        return self::$instance;
    }

    /*
    В синглтоне нужно чтобы конструктор был приватным
    что позволяет передать другому методу контроль за 
    созданием объектов этого класа
    */
    private function __construct()
    {
        if (FileBinaryMimeTypeGuesser::isSupported()) {
            $this->register(new FileBinaryMimeTypeGuesser());
        }

        if (FileinfoMimeTypeGuesser::isSupported()) {
            $this->register(new FileinfoMimeTypeGuesser());
        }
    }

    public function register(MimeTypeGuesserInterface $g) 
    {
        array_unshift($this->guessers, $g);
    }

    public function guess($path)
    {
        
        if (!is_file($path)) {
            throw new FileNotFoundException($path);
        }

        if (!is_readable($path)) {
            throw new FileNotFoundException($path);
        }

        if (sizeof($this->guessers) === 0) {
            throw new \LogicException('Not have enbled guessers');
        }
        
        foreach ($this->guessers as $g) {
            if (null !== $mime = $g->guess($path)) {
                return $mime;
            }
        }
    }
}

