<?php

namespace Europa\Response;
use ArrayIterator;
use Europa\Filter\CamelCaseSplitFilter;
use Europa\Filter\FilterInterface;
use Europa\Filter\LowerCamelCaseFilter;
use Europa\View\ViewInterface;
use IteratorAggregate;

/**
 * Counterpart to request object, outputs headers and contents
 *
 * @category Controller
 * @package  Europa
 * @author   Paul Carvosso-White <paulcarvossowhite@gmail.com>
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Http implements IteratorAggregate, ResponseInterface
{
    /**
     * The headers.
     * 
     * @var array
     */
    private $headers = [];
    
    /**
     * The camel case split filter.
     * 
     * @var CamelCaseSplitFilter
     */
    private $ccSplitFilter;
    
    /**
     * The filter that will map a view class name to a valid content type.
     * 
     * @var FilterInterface
     */
    private $contentTypeFilter;
    
    /**
     * The lower camel case filter.
     * 
     * @var LowerCamelCaseFilter
     */
    private $lccFilter;
    
    /**
     * Sets up a new response object.
     * 
     * @return Http
     */
    public function __construct()
    {
        $this->lccFilter     = new LowerCamelCaseFilter;
        $this->ccSplitFilter = new CamelCaseSplitFilter;
    }
    
    /**
     * Sets the specified request header.
     *
     * @param string $name  The name of the header.
     * @param mixed  $value The value of the header.
     *
     * @return mixed
     */
    public function __set($name, $value)
    {
        $name = $this->lccFilter->filter($name);
        
        $this->headers[$name] = $value;
    }

    /**
     * Returns the specified request header.
     *
     * @param string $name The name of the header.
     *
     * @return mixed
     */
    public function __get($name)
    {
        $name = $this->lccFilter->filter($name);
        
        if (isset($this->headers[$name])) {
            return $this->headers[$name];
        }
        
        return null;
    }
    
    /**
     * Returns whether or not the specified header is set.
     * 
     * @param string $name The header name.
     * 
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->headers[$this->lccFilter->filter($name)]);
    }
    
    /**
     * Removes the specified header if it is set.
     * 
     * @param string $name The header name.
     * 
     * @return bool
     */
    public function __unset($name)
    {
        $name = $this->lccFilter->filter($name);
        
        if (isset($this->headers[$name])) {
            unset($this->headers[$name]);
        }
    }
    
    /**
     * Sets the content type filter to use for mapping a view class to a content type.
     * 
     * @param FilterInterface $filter The filter to use.
     * 
     * @return Http
     */
    public function setContentTypeFilter(FilterInterface $filter)
    {
        $this->contentTypeFilter = $filter;
        
        return $this;
    }
    
    /**
     * Outputs the specified view.
     * 
     * @param ViewInterface $view    The view to output.
     * @param array         $context The context to render the view with.
     * 
     * @return void
     */
    public function output(ViewInterface $view = null, array $context = [])
    {
        foreach ($this->headers as $name => $value) {
            // ensure camel-cased attr converted to headers, e.g. contentType => content-type
            $name = $this->ccSplitFilter->filter($name);
            
            // uc each part
            foreach ($name as &$part) {
                $part = ucfirst($part);
            }
            
            // the header name parts are simply joined together
            header(implode('-', $name) . ': ' . $value);
        }
        
        if ($type = $this->resolveContentType($view)) {
            header('Content-Type: ' . $type);
        }
        
        if ($view) {
            echo $view->render($context);
        }
    }
    
    /**
     * Returns the iterator to use when iterating over the Response object.
     * 
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->headers);
    }
    
    /**
     * Resolves the proper content type for the specified view.
     * 
     * @param ViewInterface $view The view to resolve the content type for.
     * 
     * @return string
     */
    private function resolveContentType(ViewInterface $view = null)
    {
        if (!$view) {
            return;
        }
        
        if (isset($this->headers['contentType'])) {
            return;
        }
        
        if ($this->contentTypeFilter) {
            return $this->contentTypeFilter->filter(get_class($view));
        }
    }
}
