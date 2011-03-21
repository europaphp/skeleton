<?php

namespace Europa\View;

/**
 * Class for rendering a basic PHP view script.
 * 
 * If parsing content from a file to render, this class can be overridden
 * to provide base functionality for view manipulation while the __toString
 * method is overridden to provide custom parsing.
 * 
 * @category Views
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Php extends \Europa\View
{
    /**
     * The script to be rendered.
     * 
     * @var string
     */
    private $script = null;
    
    /**
     * Construct the view and sets defaults.
     * 
     * @param string $script The script to render.
     * @param mixed  $params The arguments to pass to the script.
     * 
     * @return \Europa\View
     */
    public function __construct($script = null, $params = null)
    {
        // set a script if defined
        $this->setScript($script)
             ->setParams($params);
    }
    
    /**
     * Parses the view file and returns the result.
     * 
     * @return string
     */
    public function __toString()
    {
        // format the script
        $script = $this->getScript();
        
        // include it and trigger an error for any exceptions since you can't throw
        // exceptions inside __toString
        if ($view = \Europa\Loader::search($script)) {
            try {
                ob_start();
                include $view;
                return ob_get_clean() . PHP_EOL;
            } catch (\Europa\Exception $e) {
                $e->trigger();
            } catch (\Exception $e) {
                $e = new Exception($e->getMessage(), $e->getCode());
                $e->trigger();
            }
        } else {
            $e = new Exception('Unable to find view "' . $script . '" in the defined loads paths.');
            $e->trigger();
        }
    }
    
    /**
     * Sets the script to be rendered.
     * 
     * @param string $script The path to the script to be rendered relative to the view path, excluding the extension.
     * 
     * @return \Europa\View
     */
    public function setScript($script)
    {
        $this->script = $script;
        return $this;
    }
    
    /**
     * Returns the set script.
     * 
     * @return string
     */
    public function getScript()
    {
        return $this->script;
    }
}