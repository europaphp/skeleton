<?php

namespace Europa\View\Helper;
use Europa\View\ViewInterface;

/**
 * A helper for generating common HTML elements and automating the monotonous tasks involved such as normalizing URIs
 * retrieving the language variables and building query string parameters from arrays.
 * 
 * @category Helpers
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Html
{
    /**
     * The view that called the helper.
     * 
     * @var ViewInterface
     */
    private $view;
    
    /**
     * Configures the HTML helper.
     * 
     * @return Html
     */
    public function __construct(ViewInterface $view)
    {
        $this->view = $view;
    }
    
    /**
     * Creates an anchor to the specified URI.
     * 
     * @param string $uri    The URI to go to.
     * @param string $label  The label applied to the link.
     * @param array  $attrs  The attributes applied to the element.
     * @param array  $params The parameters applied to the URI.
     * 
     * @return string
     */
    public function a($uri = null, $label = null, array $attributes = array(), array $params = array())
    {
        $uri   = $this->normalizeUri($uri, $params);
        $label = $label ? $this->view->lang->$label : $uri;
        return '<a href="' . $uri . '"' . $this->getAttributeString($attributes) . '>' . $label . '</a>';
    }

    /**
     * Creates an HTML image tag.
     * 
     * @param string $uri    The URI to go to.
     * @param string $alt    The alternate text applied to the image.
     * @param array  $attrs  The attributes applied to the element.
     * @param array  $params The parameters applied to the URI.
     * 
     * @return string
     */
    public function img($uri = null, $alt = null, array $attributes = array(), array $params = array())
    {
        $uri = $this->normalizeUri($uri, $params);
        $alt = $alt ? $this->view->lang->$alt : $uri;
        return '<img src="' . $uri . '" alt="' . $alt . '"' . $this->getAttributeString($attributes) . ' />';
    }
    
    /**
     * Normalizes the passed in URI and returns it.
     * 
     * @param mixed $uri The uri to normalize.
     * 
     * @return string
     */
    private function normalizeUri($uri, array $params = array())
    {
        $uri = new Uri($uri, $params);
        $uri = $uri->__toString();
        return $uri;
    }
    
    /**
     * Returns a string representing the passed in attributes.
     * 
     * @param array $attributes The attributes to convert to a string.
     * 
     * @return string|null
     */
    private function getAttributeString(array $attributes)
    {
        $attrs = array();
        foreach ($attributes as $name => $value) {
            $attrs[] = "{$name}=\"{$value}\"";
        }
        
        if ($attrs) {
            return ' ' . implode(' ', $attrs);
        }
    }
}
