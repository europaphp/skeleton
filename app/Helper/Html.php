<?php

namespace Helper;
use Europa\View;

/**
 * Contains general HTML elements that can be automated for the view.
 * 
 * @category Helpers
 * @package  Viomedia
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Html
{
    /**
     * The view that called the helper.
     * 
     * @var \Europa\View
     */
    private $view;

    /**
     * Constructs a new HTML helper.
     * 
     * @param \Europa\View $view The view that called the helper.
     * 
     * @return string
     */
    public function __construct(View $view)
    {
        $this->view = $view;
    }

    /**
     * Creates a link to the specified URI.
     * 
     * @param string $uri    The URI to go to.
     * @param string $label  The label applied to the link.
     * @param array  $params The parameters applied to the URI.
     * 
     * @return string
     */
    public function link($uri = null, $label = null, array $params = array())
    {
        $uri   = new Uri($this->view, $uri, $params);
        $uri   = $uri->__toString();
        $label = $label ? $this->view->lang->$label : $uri;
        return '<a href="' . $uri . '">' . $label . '</a>';
    }

    /**
     * Creates an HTML image tag.
     * 
     * @param string $uri    The URI to go to.
     * @param string $alt    The alternate text applied to the image.
     * @param array  $params The parameters applied to the URI.
     * 
     * @return string
     */
    public function img($uri = null, $alt = null, array $params = array())
    {
        $uri = new Uri($this->view, $uri, $params);
        $uri = $uri->__toString();
        $alt = $alt ? $this->view->lang->$alt : $uri;
        return '<img src="' . $uri . '" alt="' . $alt . '" />';
    }
}