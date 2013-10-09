<?php
namespace Symfony\Component\HttpFoundation\File\MimeType;
require_once __DIR__.'/../Exception/FileNotFoundException.php';
require_once __DIR__.'/MimeTypeGuesserInterface.php';

use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
class FileBinaryMimeTypeGuesser implements MimeTypeGuesserInterface
{
    /*
    Команда для взятия mime/type файла
    */
    private $cmd;

    /*
    Задает консольную команду для проверки mime/type файла
    */
    public function __construct($c = 'file -b --mime %s 2> /dev/null')
    {
        $this->cmd = $c;
    }

    /*
    Проверяеет возмаожно ли проверить mime/type
    с консоли
    */
    public static function isSupported()
    {
        return (
            /*
            Проверка не вигдовс ли
            (порсольку консольная програма запускается только с консоли)
            */
            !defined('PHP_WINDOWS_VERSION_BUILD')
            &&
            function_exists('passthru')
            &&
            function_exists('escapeshellarg')
        );
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
        
        /*
        Для взятия даных
        с консоли
        */
        ob_start();

        /*
        Выполнение команды
        */
        passthru(sprintf($this->cmd, escapeshellarg($path)), $return);

        /*
        0 - код успешного выполнения команды
        */
        if ($return > 0) {
            ob_end_clean();

            return null;
        }    

        $type = trim(ob_get_clean());

        /*
        Проверка то ли возвращено
        */
        if (!preg_match('#(^[1-9a-z\-]+/[1-9a-z\.\-]+)#i', $type, $match)) {
            return null;
        }

        return $match[1];

    }
}
