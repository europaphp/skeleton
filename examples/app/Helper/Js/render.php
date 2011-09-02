<?php

// sets the path prefix to "js"
$this->js('js');

// Rendering a js file based on a strict path.
// Renders: <script type="text/javascript" src="js/my/js/file.js"></script>
echo $this->js->render('my/js/file');

// Rendering a js file based on the view path from inside a child view.
// Renders: <script type="text/javascript" src="js/Controller/Index.js"></script>
echo $this->js->render($this->getScript());

// Renering multiple js files from inside a parent view.
// Renders:
//   <script type="text/javascript" src="js/Controller/Default.js"></script>
//   <script type="text/javascript" src="js/Controller/Index.js"></script>
echo $this->js->render(array(
    $this->getScript(),
    $this->getChildScript()
));