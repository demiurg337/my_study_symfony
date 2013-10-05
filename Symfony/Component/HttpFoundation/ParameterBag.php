<?
namespace Symfony\Component\HttpFoundation;

class ParameterBag implements \IteratorAggregate, \Countable
{
    protected $parameters;


    public function __construct(array $p = array()) 
    {
        $this->parameters = $p;
    }

    public function all()
    {
        return $this->parameters;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->parameters);
    }

    public function count()
    {
        return sizeof($this->parameters);
    }

    public function keys()
    {
        return array_keys($this->parameters);
    }

    public function replace(array $np = array())
    {
        $this->parameters = $np;
    }

    public function add(array $a =  array()) 
    {
        $this->parameters = array_replace($this->parameters, $a);
    }

    public function get($path, $default = null, $deep = false)
    {
        /*
        Если не искать в подмасиве (глобокий поиск $deep = true)
        или
        когда масив одномерный
        */
        if (!$deep || false === $pos = strpos($path, '[')) {
            /*
            такой способ проверки и сразу возвращать 
            */
            return array_key_exists($path,$this->parameters) ? $this->parameters[$path] : $default;
        }

        /*
        
        Сразу и прощитуется переменная pos :
            false === $pos = strpos($path, '[')) {
        */

        /*
        проверка когда строка может быит типа arr_key[key]
        пРоверяет ствует ли данный arr_key подмасив
        */
        $root = substr($path, 0, $pos);
        
        if (!array_key_exists($root, $this->parameters)) {
            return $default;
        }
        
        $val = $this->parameters[$root];
        //текущей ключ в подмасиве arr[curentKey1][curentKey2][curentKey3]
        $currentKey = null;
        /*
        Ищут все подулючи в строке
        Интересная способ инициализации переменных которые нужны цыклу
        for ($i = 0, $c = strlen($path);... 
        */
        for ($i = $pos, $c = strlen($path); $i < $c; $i++) {
            /*
            Символы можно брать за номерапи
            */
            $char = $path[$i];
            if ($char === '[') {
                if (null !== $currentKey) {
                    /*
                    Стандартная PHP ошыбка
                    */
                    throw new \InvalidArgumentException('Some str');
                }              

                $currentKey = '';
            }
            elseif ($char === ']') {
                if (null === $currentKey) {
                    throw new \InvalidArgumentException('Siashdfjkl asdf');
                }

                if (!is_array($val) || !array_key_exists($currentKey, $val)) {
                    return $default;
                }


                $val = $val[$currentKey];
                $currentKey = null;
            }
            else {
                if (null === $currentKey) {
                    throw new \InvalidArgumentException('asfjdasdlfkh sdfhajklfhaskdf');
                }
                
                $currentKey .= $char;        
            }
            
        }

        if ($currentKey !== null) {
            throw new \InvalidArgumentException('asfsadfasdf asdfasdkljfhasdjfhasjkdf ]');
        }
        
        return $val;
    }

    public function set($key, $val) 
    {
       $this->parameters[$key] = $val;  
    } 

    public function has($key)
    {
        return array_key_exists($key, $this->parameters);  
    }

    public function remove($key)
    {
        unset($this->parameters[$key]);
    }

    public function getAlnum($key, $default = '', $deep = false)
    {
        return preg_replace('/[^[:alnum:]]/', '', $this->get($key, $default, $deep));
    }

    public function filter($key, $default = null, $deep = false, $filter = FILTER_DEFAULT, $options = array()) 
    {
        $val = $this->get($key, $default, $deep);
        
        if (!is_array($options) && $options) {
            $options = array('flags' => $options);
        }

        if (is_array($options) && !isset($options['flags'])) {
            $options['flags'] = FILTER_REQUIRE_ARRAY;
        }

        return filter_var($val, $filter, $options);
    }

    public function getInt($key, $default = 0, $deep = false)
    {
        return intval($this->get($key, $default, $deep));
    }
    
    public function getDigits($key, $default = '', $deep = false) 
    {
        return str_replace(array('+', '-'), $this->filter($key, $default, $deep, FILTER_SANITIZE_NUMBER_INT));
    }    

    public function getAlpha($key, $default = '', $deep)
    {
        return preg_replace('/[^[:alpha:]]/', '', $this->get($key, $default, $deep));
    }
}     
