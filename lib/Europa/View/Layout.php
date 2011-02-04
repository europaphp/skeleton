<?php

namespace Europa\View;

/**
 * A view renderer that connects two views. One as a layout and one as the child view.
 * 
 * @category Views
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Layout extends \Europa\View
{
    /**
     * The layout to use.
     * 
     * @var \Europa\View
     */
    protected $layout = null;
    
    /**
     * The view to use.
     * 
     * @var \Europa\View
     */
    protected $view = null;
    
    /**
     * Constructs the view layout and sets layout and views.
     * 
     * @param \Europa\View $layout The layout to use.
     * @param \Europa\View $view   The view to use.
     * 
     * @return \Europa\View\Layout
     */
    public function __construct(\Europa\View $layout = null, \Europa\View $view = null)
    {
        $this->setLayout($layout)->setView($view);
    }
    
    /**
     * Renders the layout and view depending on whether or not any parts are disabled.
     * 
     * @return string
     */
    public function __toString()
    {
        // can render without layout
        if ($this->layout) {
            return $this->layout->__toString();
        }
        
        // can render without view
        if ($this->view) {
            return $this->view->__toString();
        }
        
        return '';
    }
    
    /**
     * Sets a property on both the layout and the view.
     * 
     * @param string $name  The name of the parameter to set.
     * @param mixed  $value The value being set.
     * 
     * @return void
     */
    public function __set($name, $value)
    {
        parent::__set($name, $value);
        $this->layout->$name = $value;
        $this->view->$name   = $value;
    }
    
    /**
     * Unsets the specified property on both the layout and the view.
     * 
     * @param string $name The property to unset.
     * 
     * @return void
     */
    public function __unset($name)
    {
        parent::__unset($name);
        unset($this->layout->$name);
        unset($this->view->$name);
    }
    
    /**
     * Sets the layout to use.
     * 
     * @param \Europa\View $layout The layout to use.
     * 
     * @return \Europa\View\Layout
     */
    public function setLayout(\Europa\View $layout = null)
    {
        $this->layout = $layout;
        return $this;
    }
    
    /**
     * Returns the layout.
     * 
     * @return \Europa\View
     */
    public function getLayout()
    {
        return $this->layout;
    }
    
    /**
     * Sets the view to use.
     * 
     * @param \Europa\View $view The view to use.
     * 
     * @return \Europa\View\layout
     */
    public function setView(\Europa\View $view = null)
    {
        $this->view = $view;
        return $this;
    }
    
    /**
     * Returns the view.
     * 
     * @return \Europa\View
     */
    public function getView()
    {
        return $this->view;
    }
    
    /**
     * Sets the name of the property to bind the view on the layout to at the time of rendering.
     * 
     * @param string $name The name of the property.
     * 
     * @return \Europa\View\Layout
     */
    public function setLayoutViewProperty($name)
    {
        $this->layoutViewProperty = $name;
        return $this;
    }
    
    /**
     * Returns the name of the property that the view is bound to on the layout.
     * 
     * @return string
     */
    public function getLayoutViewProperty()
    {
        return $this->layoutViewProperty;
    }
}