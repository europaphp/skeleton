<?php

namespace Europa;

/**
 * Europa fluid string manipulation class.
 * 
 * @category String
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class String implements \Countable
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
    private $string;

    /**
     * Constructs a new string object from the passed in string.
     *
     * @param string $string The string to manipulate.
     * 
     * @return \Europa\String
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
        $this->string = (string) $string;
    }

    /**
     * Converts the string object back to a string.
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->string;
    }
    
    /**
     * Formats a string based on the replacements.
     * 
     * @return \Europa\String
     */
    public function format(array $replacements, $openChar = null, $closeChar = null)
    {
        $openChar  = $openChar  ? $openChar  : self::$formatOpenChar;
        $closeChar = $closeChar ? $closeChar : self::$formatCloseChar;
        foreach ($replacements as $name => $value) {
            $this->string = str_replace(
                $openChar . $name . $closeChar,
                (string) $value,
                $this->string
            );
        }
        return $this;
    }
    
    /**
     * Replaces the first replacement with the second replacement. Behaves
     * exactly like str_replace() because, well, it uses it.
     * 
     * @param mixed $search  The string(s) to search for.
     * @param mixed $replace The string(s) to replace with.
     * 
     * @return \Europa\String
     */
    public function replace($search, $replace)
    {
        $this->string = str_replace($search, $replace, $this->string);
        return $this;
    }
    
    /**
     * Splits upper-case words using the specified separator.
     * 
     * @param string $separator The separator to separate camel words with. Defaults to empty space.
     * 
     * @return \Europa\String
     */
    public function splitUcWords($separator = '')
    {
        $parts = array('');
        foreach (str_split($this->string) as $char) {
            $lower = strtolower($char);
            if ($char === $lower) {
                $parts[count($parts) - 1] .= $lower;
            } else {
                $parts[] = $lower;
            }
        }
        $this->string = implode($separator, $parts);
        return $this;
    }
    
    /**
     * Formats the string into a valid class name according to convention.
     * 
     * @return \Europa\String
     */
    public function toClass()
    {
        // normalize namespace separators
        $this->string = str_replace(array(DIRECTORY_SEPARATOR, '/'), '\\', $this->string);
        
        // split into class namespaces
        $parts     = explode('\\', $this->string);
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
        
        $this->string = '\\' . implode('\\', $partsTemp);
        return $this;
    }
    
    /**
     * Formats the string into a valid method name according to convention.
     * 
     * @return \Europa\String
     */
    public function toMethod()
    {
        return $this->toClass()->replace('\\', '')->lcFirst();
    }
    
    /**
     * Formats the string into a valid property name according to convention.
     * 
     * @return \Europa\String
     */
    public function toProperty()
    {
        return $this->toMethod();
    }

    /**
     * Same as PHP trim() function, but put in to allow for chaining.
     *
     * @param string $charList Same as the char-list in PHP's trim() function.
     * 
     * @return string.
     */
    public function trim($charList = null)
    {
        $this->string = trim($this->string, $charList);
        return $this;
    }

    /**
     * Same as PHP ltrim() function, but put in to allow for chaining.
     *
     * @param string $charList Same as the charlist in PHP's ltrim() function.
     * 
     * @return string.
     */
    public function ltrim($charList = null)
    {
        $this->string = ltrim($this->string, $charList);
        return $this;
    }

    /**
     * Same as PHP rtrim() function, but put in to allow for chaining.
     *
     * @param string $charList Same as the charlist in PHP's rtrim() function.
     * 
     * @return \Europa\String
     */
    public function rtrim($charList = null)
    {
        $this->string = rtrim($this->string, $charList);
        return $this;
    }
    
    /**
     * Makes the first letter lowercase.
     * 
     * @return \Europa\String
     */
    public function lcFirst()
    {
        $this->string[0] = strtolower($this->string[0]);
        return $this;
    }
    
    /**
     * Makes the first letter uppercase.
     * 
     * @return \Europa\String
     */
    public function ucFirst()
    {
        $this->string[0] = strtoupper($this->string[0]);
        return $this;
    }
    
    /**
     * Lowercases each word in the string.
     * 
     * @return \Europa\String
     */
    public function lcWords()
    {
        $parts = explode(' ', $this->string);
        foreach ($parts as &$part) {
            $part = lcfirst($part);
        }
        $this->string = implode(' ', $parts);
        return $this;
    }
    
    /**
     * Makes each word start with an uppercase character.
     * 
     * @return \Europa\String
     */
    public function ucWords()
    {
        $this->string = ucwords($this->string);
        return $this;
    }
    
    /**
     * Makes the string lowercase.
     * 
     * @return \Europa\String
     */
    public function toLowercase()
    {
        $this->string = strtolower($this->string);
        return $this;
    }
    
    /**
     * Makes the string uppercase.
     * 
     * @return \Europa\String
     */
    public function toUppercase()
    {
        $this->string = strtoupper($this->string);
        return $this;
    }
    
    /**
     * Transforms the string into a url slug.
     * 
     * @return \Europa\String
     */
    public function slug($separator = '-')
    {
        $str = $this->string;
        $str = preg_replace('/[^a-zA-Z0-9]/', $separator, $str);
        $str = preg_replace('/\\' . $separator . '{2,}/', $separator, $str);
        $this->string = $str;
        $this->trim($separator);
        return $this;
    }
    
    /**
     * Takes a value and type casts it. Strings such as 'true' or 'false' 
     * will be converted to a boolean value. Numeric strings will be converted
     * to integers or floats and empty strings are converted to NULL values.
     *         
     * @param mixed $val The value to cast and return.
     * 
     * @return mixed
     */
    public function cast()
    {
        $val = $this->string;
        if (strtolower($val) == 'true') {
            return true;
        }
        if (strtolower($val) == 'false') {
            return false;
        }
        if (!$val || strtolower($val) == 'null') {
            return null;
        }
        if (isstring($val) && is_numeric($val)) {
            if (strpos($val, '.') === false) {
                $val = (int) $val;
            } else {
                $val = (float) $val;
            }
        }
        return $val;
    }
    
    /**
     * Multi-byte safe. Calculates and returns number of characters in a string.
     * 
     * @return int
     */
    public function count()
    {
        return mb_strlen($this->string);
    }

    /**
     * Creates a new string. Same as calling new \Europa\String($string).
     *
     * @param string $string The string the object should represent.
     * 
     * @return string
     */
    public static function create($string = '')
    {
        return new self($string);
    }
}