<?php

/**
 * Europa fluid string manipulation class.
 * 
 * @category String
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
class Europa_String
{
    /**
     * The opening character in a format replacement.
     * 
     * @var string
     */
    public static $formatOpenChar = ':';
    
    /**
     * The closing character in a format replacement.
     * 
     * @var string
     */
    public static $formatCloseChar = '';
    
    /**
     * Holds a reference to the current string.
     * 
     * @var string
     */
    private $_string;

    /**
     * Constructs a new string object from the passed in string.
     *
     * @param string $string The string to manipulate.
     * @return Europa_String
     */
    public function __construct($string = '')
    {
        if ($string === true) {
            $string = 'true';
        } elseif ($string === false) {
            $string = 'false';
        } elseif (is_numeric($string)) {
            $string = (string) $string;
        } elseif (is_array($string)) {
            $string = serialize($string);
        } elseif (is_null($string)) {
            $string = 'null';
        }
        
        $this->_string = (string) $string;
    }

    /**
     * Converts the string object back to a string.
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->_string;
    }
    
    /**
     * Formats a string based on the replacements.
     * 
     * @return Europa_String
     */
    public function format(array $replacements, $openChar = null, $closeChar = null)
    {
        $openChar  = $openChar  ? $openChar  : self::$formatOpenChar;
        $closeChar = $closeChar ? $closeChar : self::$formatCloseChar;
        foreach ($replacements as $name => $value) {
            $this->_string = str_replace(
                $openChar . $name . $closeChar,
                (string) $value,
                $this->_string
            );
        }
        return $this;
    }
    
    /**
     * Replaces the first replacement with the second replacement. Behaves
     * exactly like str_replace() because, well, it uses it.
     * 
     * @param mixed $search The string(s) to search for.
     * @param mixed $replace The string(s) to replace with.
     * @return Europa_String
     */
    public function replace($search, $replace)
    {
        $this->_string = str_replace($search, $replace, $this->_string);
        return $this;
    }
    
    /**
     * Formats the string into a valid class name according to convention.
     * 
     * @return Europa_String
     */
    public function toClass()
    {
        // normalize namespace separators
        $this->_string = str_replace(array(DIRECTORY_SEPARATOR, '/'), '_', $this->_string);
        
        // split into class namespaces
        $parts     = explode('_', $this->_string);
        $partsTemp = array();
        foreach ($parts as $part) {
            $part = trim($part);
            if (!$part) {
                continue;
            }
            
            // only allow alpha-numeric characters
            $subParts     = preg_split('/[^a-zA-Z0-9]/', $part);
            $subPartsTemp = array();
            foreach ($subParts as $subPart) {
                $subPart = trim($subPart);
                if (!$subPart) {
                    continue;
                }
                $subPartsTemp[] = ucfirst($subPart);
            }
            $partsTemp[] = implode('', $subPartsTemp);
        }
        
        $this->_string = implode('_', $partsTemp);
        return $this;
    }
    
    /**
     * Formats the string into a valid method name according to convention.
     * 
     * @return Europa_String
     */
    public function toMethod()
    {
        $this->toLowercase();
        $this->toClass();
        $this->lcfirst();
        $this->_string = str_replace('_', '', $this->_string);

        return $this;
    }
    
    /**
     * Formats the string into a valid property name according to convention.
     * 
     * @return Europa_String
     */
    public function toProperty()
    {
        return $this->toMethod();
    }

    /**
     * Same as PHP trim() function, but put in to allow for chaining.
     *
     * @param string $charList Same as the char-list in PHP's trim() function.
     * @return string.
     */
    public function trim($charList = null)
    {
        $this->_string = trim($this->_string, $charList);

        return $this;
    }

    /**
     * Same as PHP ltrim() function, but put in to allow for chaining.
     *
     * @param string $charList Same as the charlist in PHP's ltrim() function.
     * @return string.
     */
    public function ltrim($charList = null)
    {
        $this->_string = ltrim($this->_string, $charList);

        return $this;
    }

    /**
     * Same as PHP rtrim() function, but put in to allow for chaining.
     *
     * @param string $charList Same as the charlist in PHP's rtrim() function.
     * @return Europa_String
     */
    public function rtrim($charList = null)
    {
        $this->_string = rtrim($this->_string, $charList);
        return $this;
    }
    
    /**
     * Makes the first letter uppercase.
     * 
     * @return Europa_String
     */
    public function ucfirst()
    {
        $this->_string[0] = strtoupper($this->_string[0]);
        return $this;
    }
    
    /**
     * Makes the first letter lowercase.
     * 
     * @return Europa_String
     */
    public function lcfirst()
    {
        $this->_string[0] = strtolower($this->_string[0]);
        return $this;
    }
    
    /**
     * Makes each word start with an uppercase character.
     * 
     * @return Europa_String
     */
    public function ucwords()
    {
        $this->_string = ucwords($this->_string);
        return $this;
    }
    
    /**
     * Makes the string lowercase.
     * 
     * @return Europa_String
     */
    public function toLowercase()
    {
        $this->_string = strtolower($this->_string);
        
        return $this;
    }
    
    /**
     * Makes the string uppercase.
     * 
     * @return Europa_String
     */
    public function toUppercase()
    {
        $this->_string = strtoupper($this->_string);
        
        return $this;
    }
    
    /**
     * Transforms the string into a url slug.
     * 
     * @return Europa_String
     */
    public function slug($separator = '-')
    {
        $str = $this->_string;
        $str = preg_replace('/[^a-zA-Z0-9]/', $separator, $str);
        $str = preg_replace('/\\' . $separator . '{2,}/', $separator, $str);
        $this->_string = $str;
        $this->trim($separator);
        
        return $this;
    }
    
    /**
     * Takes a value and type casts it. Strings such as 'true' or 'false' 
     * will be converted to a boolean value. Numeric strings will be converted
     * to integers or floats and empty strings are converted to NULL values.
     *         
     * @param mixed $val The value to cast and return.
     * @return mixed
     */
    public function cast()
    {
        $val = $this->_string;
        if (strtolower($val) == 'true') {
            return true;
        }
        if (strtolower($val) == 'false') {
            return false;
        }
        if (!$val || strtolower($val) == 'null') {
            return null;
        }
        if (is_string($val) && is_numeric($val)) {
            if (strpos($val, '.') === false) {
                $val = (int) $val;
            } else {
                $val = (float) $val;
            }
        }
        return $val;
    }

    /**
     * Creates a new string. Same as calling new Europa_String($string).
     *
     * @param string $string The string the object should represent.
     * @return string
     */
    public static function create($string = '')
    {
        return new self($string);
    }
}