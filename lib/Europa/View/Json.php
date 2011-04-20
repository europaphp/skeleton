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
class Json extends View
{
    /**
     * Constructs the view and sets parameters.
     * 
     * @param mixed $params The parameters to set.
     * 
     * @return \Europa\View\Json
     */
    public function __construct($params = null)
    {
        $this->setParams($params);
    }
    
    /**
     * JSON encodes the parameters on the view and returns them.
     * 
     * @return string
     */
    public function render()
    {
        if (!headers_sent()) {
            header('Content-Type: Application/JSON');
        }
        return json_encode($this->getParams());
    }
}