<?php

namnespace Helper;
use Europa\ServiceLocator;
use Europa\View\Php;

/**
 * Creates and renders a partial representing the specified view file.
 * 
 * @category ViewHelpers
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Partial
{
    /**
     * The string result of the dispatch call.
     * 
     * @var string
     */
    private $result;

    /**
     * Constructs a dispatch helper and passes in the required parameters. The request can be auto-detected
     * if not specified, or overridden if specified.
     * 
     * @param \Europa\View $view   The view that called the helper.
     * @param string       $script The path to the script to render.
     * @param array        $params An array of parameters to pass off to the new view.
     * 
     * @return DispatchHelper
     */
    public function __construct(Php $view, $script, array $params = array())
    {
        $this->result = ServiceLocator::getInstance()->create('partialView');
        $this->result = $this>view->setScript($script)->setParams($params)->render();
    }

    /**
     * Returns the dispatch result as a string.
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->result;
    }
}