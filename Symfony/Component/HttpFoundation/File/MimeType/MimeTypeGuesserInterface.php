<?php

namespace Symfony\Component\HttpFoundation\File\MimeType;
//require_once __DIR__.'/../Exception/FileNotFoundException.php';
//use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
interface MimeTypeGuesserInterface
{

    public function guess($path);
}
