Passing named parameters to the language variable "h1". For example, if "h1" contained "The title is: :title.", ":title" would be replaced with whatever "$this->title" contains.
<?php echo $this->lang->h1(array('title' => $this->title)); ?>

Passing named positional parameters. You can use whatever sprintf arguments would take. For example: "The title is: %s."
<?php echo $this->lang->h1(array($this->title)); ?>