<?php

// sets the path prefix to "css"
$this->css('css');

// Rendering a css file based on a strict path.
// Renders: <link rel="stylesheet" type="text/css" href="css/my/css/file.css" />
echo $this->css->render('my/css/file');

// Rendering a css file based on the view path.
// Renders: <link rel="stylesheet" type="text/css" href="css/Controller/Index.css" />
echo $this->css->render($this->getScript());

// Renering multiple css files.
// Renders:
//   <link rel="stylesheet" type="text/css" href="css/Controller/Default.css" />
//   <link rel="stylesheet" type="text/css" href="css/Controller/Index.css" />
echo $this->css->render(array(
    $this->getScript(),
    $this->getChildScript()
));