<?php

namespace Europa\View;
use Europa\View;

/**
 * A view class for rendering JSON data from bound parameters.
 * 
 * @category Views
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Xml implements ViewInterface
{
    /**
     * Configuration array.
     * 
     * @var array
     */
    private $config = [
        'declaration' => true,
        'encoding'    => 'UTF-8',
        'indent'      => true,
        'spaces'      => 2,
        'version'     => '1.0'
    ];
    
    /**
     * Sets up the XML view renderer.
     * 
     * @param strign $config The configuration array.
     * 
     * @return Xml
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->config, $config);
    }
    
    /**
     * JSON encodes the parameters on the view and returns them.
     * 
     * @return string
     */
    public function render(array $context = [])
    {
        $str = '';
        
        if ($this->config['declaration']) {
            $str = '<?xml version="'
                . $this->config['version']
                . '" encoding="'
                . $this->config['encoding']
                . '" ?>'
                . PHP_EOL;
        }
        
        foreach ($context as $name => $content) {
            $str .= $this->renderNode($name, $content);
        }
        
        return $str;
    }
    
    /**
     * Renders a particular node.
     * 
     * @param string $name    The node name.
     * @param string $content The node content.
     * 
     * @return string
     */
    private function renderNode($name, $content, $level = 0)
    {
        $ind = $this->indent($level);
        $str = $ind . "<{$name}>";
        
        if (is_array($content) || is_object($content)) {
            $str .= PHP_EOL;
            foreach ($content as $k => $v) {
                $str .= $this->renderNode($k, $v, $level + 1);
            }
            $str .= $ind;
        } else {
            $str .= $content;
        }
        
        $str .= "</{$name}>";
        $str .= PHP_EOL;
        
        return $str;
    }
    
    /**
     * Indents the XML using the specified configuration settings.
     * 
     * @param int $level The level to indent to.
     * 
     * @return string
     */
    private function indent($level)
    {
        $char = $this->spaces ? str_repeat(' ', $this->spaces) : "\t";
        return str_repeat($char, $level);
    }
}
