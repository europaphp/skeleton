<?php

/**
 * A view renderer that connects two views. One as a layout and one as the child view.
 * 
 * @category  Views
 * @package   Europa
 * @author    Trey Shugart <treshugart@gmail.com>
 * @copyright (c) 2010 Trey Shugart
 * @link      http://europaphp.org/license
 */
class Europa_View_Layout extends Europa_View
{
	/**
	 * The layout to use.
	 * 
	 * @var Europa_View
	 */
	protected $_layout = null;
	
	/**
	 * The view to use.
	 * 
	 * @var Europa_View
	 */
	protected $_view = null;
	
	/**
	 * The property that the view is bound on the layout to.
	 * 
	 * @var string
	 */
	protected $_layoutViewProperty = 'view';
	
	/**
	 * Constructs the view layout and sets layout and views.
	 * 
	 * @param Europa_View $layout The layout to use.
	 * @param Europa_View $view The view to use.
	 * @return Europa_View_Layout
	 */
	public function __construct(Europa_View $layout = null, Europa_View $view = null)
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
		if ($this->_layout) {
			$this->_layout{$this->_layoutViewProperty} = $this->_view;
			return $this->_layout->__toString();
		}
		
		if ($this->_view) {
			return $this->_view->__toString();
		}
		
		return '';
	}
	
	/**
	 * Sets a property on both the layout and the view.
	 * 
	 * @param string $name The name of the parameter to set.
	 * @param mixed $value The value being set.
	 * @return void
	 */
	public function __set($name, $value)
	{
		parent::__set($name, $value);
		$this->_layout->$name = $value;
		$this->_view->$name   = $value;
	}
	
	/**
	 * Unsets the specified property on both the layout and the view.
	 * 
	 * @param string $name The property to unset.
	 * @return void
	 */
	public function __unset($name)
	{
		parent::__unset($name);
		unset($this->_layout->$name);
		unset($this->_view->$name);
	}
	
	/**
	 * Sets the layout to use.
	 * 
	 * @param Europa_View $layout The layout to use.
	 * @return Europa_View_Layout
	 */
	public function setLayout(Europa_View $layout = null)
	{
		$this->_layout = $layout;
		return $this;
	}
	
	/**
	 * Returns the layout.
	 * 
	 * @return Europa_View
	 */
	public function getLayout()
	{
		return $this->_layout;
	}
	
	/**
	 * Sets the view to use.
	 * 
	 * @param Europa_View $view The view to use.
	 * @return Europa_View_Layout
	 */
	public function setView(Europa_View $view = null)
	{
		$this->_view = $view;
		return $this;
	}
	
	/**
	 * Returns the view.
	 * 
	 * @return Europa_View
	 */
	public function getView()
	{
		return $this->_view;
	}
	
	/**
	 * Sets the name of the property to bind the view on the layout to at the
	 * time of rendering.
	 * 
	 * @param string $name The name of the property.
	 * @return Europa_View_Layout
	 */
	public function setLayoutViewProperty($name)
	{
		$this->_layoutViewProperty = $name;
		return $this;
	}
	
	/**
	 * Returns the name of the property that the view is bound to on the layout.
	 * 
	 * @return string
	 */
	public function getLayoutViewProperty()
	{
		return $this->_layoutViewProperty;
	}
}