<?php
namespace Symfony\Component\HttpFoundation\File\MimeType;

require_once __DIR__.'/ExtensionGuesserInterface.php';
require_once __DIR__.'/MimeTypeExtensionGuesser.php';
/*
Singlton
*/
class ExtensionGuesser implements ExtensionGuesserInterface
{
    private static $instance = null;
    protected $guessers = array();
    
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        $this->register(new MimeTypeExtensionGuesser());
    }

    public function register(ExtensionGuesserInterface $g)
    {
        array_unshift($this->guessers, $g);
    }

    public function guess($mimeType)
    {
        foreach($this->guessers as $g) {
            $ext = $g->guess($mimeType);
            
            if (null !== $ext) {
                return $ext;    
            }
        }

        return $ext;
    }
}
