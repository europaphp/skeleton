<?php

class Europa_Install_Data_Php implements Europa_Install_Data
{
    protected $file;
    
    protected $version;
    
    public function __construct($file)
    {
        $this->file = $file;
    }
    
    public function save()
    {
        $code  = "<?php\n";
        $code .= "\n";
        $code .= "\$version = '{$this->version}'";
        file_put_contents($this->file, $code);
    }
}