<?php

/**
 * A standard controller base class that implements a layout view system in
 * a one-controller-per-action environment.
 * 
 * @category  Controllers
 * @package   Europa
 * @author    Trey Shugart <treshugart@gmail.com>
 * @copyright (c) 2010 Trey Shugart
 * @link      http://europaphp.org/license
 */
abstract class Europa_Controller_Standard extends Europa_Controller
{
    /**
     * Constructs the controller and sets the request to use.
     * 
     * @param Europa_Request $request The request to use.
     * @return Europa_Controller_Standard
     */
    public function __construct($request)
    {
        // parent stuff
        parent::__construct($request);
        
        // map properties
        $this->_mapRequestToProperties($request);
    }
    
    /**
     * Sets properties from the request onto the class. If a property exists that
     * doesn't have a default value and it doesn't exist in the request, then an
     * exception is thrown.
     * 
     * @return void
     */
    protected function _mapRequestToProperties(Europa_Request $request)
    {
        $params = array();
        foreach ($request->getParams() as $name => $param) {
            $params[strtolower($name)] = $param;
        }
        
        $class = new ReflectionClass($this);
        foreach ($class->getProperties() as $property) {
            $normalcase = $property->getName();
            $lowercase  = strtolower($normalcase);
            if (isset($params[$lowercase])) {
                $this->$normalcase = $params[$lowercase];
            } elseif (!isset($this->$normalcase)) {
                throw new Europa_Controller_Exception(
                    "Required request parameter {$normalcase} was not defined."
                );
            }
            
            // cast the parameter if it is scalar
            if (is_scalar($this->$normalcase)) {
                $this->$normalcase = Europa_String::create($this->$normalcase)->cast();
            }
        }
    }
}