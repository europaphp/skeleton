<?php

class UnitTest_Europa_View extends Europa_Unit
{
	public function __construct()
	{
		$this->tempView   = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'temp-view.php';
		$this->tempHelper = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'TempHelper.php';
		
		file_put_contents($this->tempView, 'test');
		file_put_contents($this->tempHelper, '<?php class TempHelper { public function Temp() { return true; } }');
		
		$this->view = new Europa_View(array(
				'viewPath'   => dirname(__FILE__),
				'helperPath' => dirname(__FILE__)
			));
	}
	
	public function testLoadingAndRenderingView()
	{
		// set the temp view to be rendered
		$this->view->render('temp-view');
		
		// trigger the __toString magic method
		$view = $this->view->__toString();
		
		// remove the temporary view
		unlink(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'temp-view.php');
		
		return (bool) preg_match('/test/', $view);
	}
	
	public function testLoadingAndExecutingHelper()
	{
		// helper names can start with a lowercase or uppercase character
		$ret = $this->view->temp();
		
		// remove the temporary file
		unlink($this->tempHelper);
		
		return $ret;
	}
}