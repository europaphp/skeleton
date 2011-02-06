<?php

class Test_View_Children extends Testes_Test
{
    private $parent;
    
    public function setUp()
    {
        $parent = new \Europa\View\Php;
        $chris  = new \Europa\View\Php;
        $mary   = new \Europa\View\Php;
        $rufus  = new \Europa\View\Php;
        $mika   = new \Europa\View\Php;
        $jess   = new \Europa\View\Php;
        $apple  = new \Europa\View\Php;
        $bob    = new \Europa\View\Php;
        $gonzo  = new \Europa\View\Php;
        
        $parent->setChild('chris', $chris);
        $parent->setChild('mary', $mary);
        $mary->setChild('rufus', $rufus);
        $chris->setChild('mika', $mika);
        $rufus->setChild('jess', $jess);
        $mika->setChild('apple', $apple);
        $jess->setChild('bob', $bob);
        $apple->setChild('gonzo', $gonzo);
        
        $this->parent = $parent;
    }
    
    public function testGetChild()
    {
        $this->assert($this->parent->getChild('chris') instanceof \Europa\View, 'Could not get child view.');
    }
    
    public function testChildrenCount()
    {
        $this->assert(count($this->parent->getChildren()) === 2, 'The number of children do not match.');
    }
    
    public function testDescendantCount()
    {
        $this->assert(count($this->parent->getDescendants()) === 8, 'Not all of the descendants were returned.');
    }
}