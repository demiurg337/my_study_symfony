<?php
class Request {
    public static function createFromGlobals()
    {
       /*
       * Создает новый екземпляр текущего
       * класа
       */
       $request = new static(); 
    }
}

