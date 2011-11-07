<?php

namespace Europa\View\Helper;

/**
 * A helper for generating JavaScript script tags.
 * 
 * @category Helpers
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Js extends Script
{
    /**
     * The PHP view variables to register for JavaScript.
     * 
     * @var array
     */
    private $register = array();
    
    /**
     * The namespace to register the variables under.
     * 
     * @var string
     */
    private $registerNs = null;
    
    /**
     * Registers variables to be passed from the view to JavaScript.
     * 
     * @param array $vars The variables to pass to JavaScript.
     * @param array $ns   The namespace to use.
     * 
     * @return \Helper\Js
     */
    public function register(array $vars, $ns = null)
    {
        $this->register   = $vars;
        $this->registerNs = $ns;
        return $this;
    }
    
    /**
     * Compiles the specified file into a tag.
     * 
     * @param string $file The file to compile.
     * 
     * @return string
     */
    protected function compile($file)
    {
        $vars = '';
        if ($this->register) {
            $vars .= '<script type="text/javascript">' . PHP_EOL;
            
            $ns = 'window';
            if ($this->registerNs) {
                foreach (explode('.', $this->registerNs) as $subNs) {
                    $ns   .= '[' . $subNs . ']';
                    $vars .= $ns . ' = {};' . PHP_EOL;
                }
            }
            
            foreach ($this->register as $name => $value) {
                $vars .= $ns . '[' . json_encode($name) . '] = ' . $this->toJson($value) . ';' . PHP_EOL;
            }
            
            $vars .= '</script>' . PHP_EOL;
        }
        
        return $vars . "<script type=\"text/javascript\" src=\"{$file}.js\"></script>";
    }
    
    /**
     * Converts any variable to JSON format making sure that it can be converted before doing the conversion.
     * 
     * @param mixed $any Any type of value to be converted to JSON format.
     * 
     * @return string
     */
    private function toJson($any)
    {
    	return json_encode($this->makeJsonEncodable($any));
    }

    /**
     * Makes sure that the passed in value can be JSON encoded. This includes any object instance that may be
     * traversable. If the value is not an array or object, it is simply passed through.
     * 
     * @param mixed $any Any type of value to convert to a JSON encodable array.
     * 
     * @return mixed
     */
    private function makeJsonEncodable($any)
    {
    	if (is_array($any) || is_object($any)) {
    	    $arr = array();
    		foreach ($any as $i => $v) {
    		    $arr[$i] = $this->makeJsonEncodable($v);
    		}
    		$any = $arr;
    	}
    	
    	return $any;
    }
}
