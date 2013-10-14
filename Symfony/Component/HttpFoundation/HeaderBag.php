<?php

namespace Symfony\Component\HttpFoundation;

class HeaderBag implements \Countable, \IteratorAggregate
{
    protected $headers;
    protected $cacheControl;

    public function __construct(array $headers = array())
    {
        $this->headers = array();
        $this->cacheControl = array();

        foreach($headers as $key => $val) {
            $this->set($key, $val);
        }
    }

    public function __toString()
    {
        if (!$this->headers) {
            return '';
        }
        
        /*
        Вызывается функция (стандартная PHP) котороая узнает длину ключа элемента массива
        */
        $max = max(array_map('strlen', $this->headers)) + 1;
        $content = '';
        ksort($this->headers);
        foreach ($this->headers as $name => $val) {
            /*
            Сначала Разбивает строку, потом к разбитым эолементам изменяеи первую букву
            и потом опять сцепливается
            */
            $name = implode('-', array_map('ucfirst', explode('-', $name)));
            foreach ($val as $v) {
                $content .=  $max.'  header='.$name.' val='. $v;
            }
        }

        return $content;

    }

    public function all()
    {
        return $this->headers;
    }

    public function keys()
    {
        return array_keys($this->headers);
    }

    public function add (array $h) 
    {
        foreach ($h as $key => $val) {
            $this->set($key, $val);
        }
    }

    public function replace(array $headers = array())
    {
        $this->headers = array();
        $this->add($headers);
    }
    /*
    * $first Берет только первое значение из нескольких возможных
    */
    public function get($key, $default = null, $first = true)
    {
        $key = strtr(strtolower($key), '_', '-');

        if (! array_key_exists($key, $this->headers)) {
            if (null === $default) {
                return $first ? null : array();
            }

            return $first ? $default : array($default);
        }

        if ($first) {
            return sizeof($this->headers[$key]) > 0 ? $this->headers[$key][0] : $default;
        }

        return $this->headers[$key];
    }
    
    public function has($key) 
    {
        return array_key_exists(strtr(strtolower($key), '_', '-'), $this->headers);
    }

    public function contains($key, $val)
    {
        return in_array($val, $this->get($key, null, false));
    }

    public function remove($key) {
        $key = strtr(strtolower($key), '_', '-');
        unset($this->headers[$key]);
        
        if ('cache-control' === $key) {
            $this->cacheControl = array();
        }  
    
    }

    public function getDate($key, \DateTime $default = null){
        if (null === $val = $this->get($key)) {
            return $default;
        }

        if (false === $date = \DateTime::createFromFormat(DATE_RFC2822, $val)) {
            throw new \RuntimeException('Not valiad value header');
        }

        return $date;
    }
    
    public function parseCacheControl($header)
    {
        preg_match_all('#([A-Za-z][A-Za-z-_]*)\s*(?:=(?:"([^"]*)"|([^ \t";,]*)))?#', $header, $matches, PREG_SET_ORDER);
        $cacheControl = array();
        
        foreach($matches as $match) {
            $cacheControl[strtolower($match[1])] = isset($match[3]) ? $match[3] : (isset($match[2]) ? $match[2] : true);
        }

        return $cacheControl;
    }

    public function set($key, $val, $replace = true)
    {
        $key = strtr(strtolower($key), '_', '-');
        $val = array_values((array) $val);

        if (true === $replace || !isset($this->headers[$key])) {
            $this->headers[$key] = $val;
        }
        else {
            $this->headers[$key] = array_merge($this->headers[$key], $val);
        }

        if ($key === 'cache-control') {
            $this->cacheControl = $this->parseCacheControl($val[0]);
        }
    }

    protected function getCacheControlHeader(){
        $parts = array();
        ksort($this->cacheControl);

        foreach($this->cacheControl as $key => $val) {
            if (true === $val) {
                $parts[] = $key;
            }
            else {
                if (preg_match('#[^A-Za-z0-9_.-]#', $val)) {
                    $val = '"'.$val.'"';
                } 
                
                $parts[] = "$key=$val";
            }
        }
        
        return implode(', ', $parts);
    }

    public function addCacheControlDirective($key, $val = true) {
        $this->cacheControl[$key] = $val;
        $this->set('Cache-Control', $this->getCacheControlHeader());    
    }   

    public function hasCacheControlDirective($key){
        return array_key_exists($key, $this->cacheControl);
    }

    public function getCacheControlDirective($key) {
    
        return array_key_exists($key, $this->cacheControl) ? $this->cacheControl[$key] : null;
    }

    public function removeCacheControlDirective($key) {
        unset($this->cacheControl[$key]);    
        $this->set('Cache-Control', $this->getCacheControlHeader());    
    }

    public function getIterator(){
        return new \ArrayIterator($this->headers);
    }
    
    public function count() {
        return sizeof($this->headers);
    }
}
