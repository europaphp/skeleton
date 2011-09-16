<?php

namespace Europa\View;
use Kafe\Template;

/**
 * Extends the main Europa View script engine to implement Kafe Haml templates.
 * 
 * @category Views
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 * @see      https://github.com/treshugart/Kafe
 */
class Kafe extends ViewScriptAbstract
{
    /**
     * Executes the Kafe Template.
     * 
     * @return string
     */
    public function execute()
    {
        $__template = new Template;
        $__template = $__template->renderFile($this->getScriptPathname());
        
        ob_start();
        $out = eval(' ?>' . $__template);
        return ob_get_clean();
    }
}