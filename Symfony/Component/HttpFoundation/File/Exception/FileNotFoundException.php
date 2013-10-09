<?php
namespace Symfony\Component\HttpFoundation\File\Exception;
require_once __DIR__.'/FileException.php';

class FileNotFoundException extends FileException
{
    public function __construct($path)
    {
        parent::__construct(sprintf('Some str % when error', $path));
    }
}
