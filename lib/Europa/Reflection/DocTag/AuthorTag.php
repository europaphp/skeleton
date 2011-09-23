<?php

namespace Europa\Reflection\DocTag;
use Europa\Reflection\DocTag;

/**
* Represents a docblock author tag.
*
* @category Reflection
* @package  Europa
* @author   Trey Shugart <treshugart@gmail.com>
* @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
*/
class AuthorTag extends DocTag
{
    /**
    * Name of the author
    * 
    * @var string
    */
    protected $name;
    
    /**
    * Return the tag object type
    * 
    * @return string
    */
    public function tag()
    {
        return 'author';
    }
    
    /**
    * Set the name of the author
    * 
    * @param string $name Name of the author
    * 
    * @return void
    */
    public function setName($name)
    {
        $this->name = $name;
    }
    
    /**
    * Return the name of the author
    * 
    * @return string
    */
    public function getName()
    {
        return $this->name;
    }
    
    /**
    * Parse the author tag
    * 
    * @param string Author tag
    * 
    * @return void
    */
    public function parse($tagString)
    {
        // use default parsing for generating the name and doc string
        parent::parse($tagString);

        // a doc string must be specified
        if (!$this->tagString) {
            throw new \Europa\Reflection\Exception('A valid author type must be specified. None given.');
        }

        // split in to tag/author name parts
        $parts = preg_replace('/\s+/', ' ', $this->tagString);
        $parts = preg_split('/\s+/', $this->tagString, 2);

        // require a var name
        if (!isset($parts[0])) {
            throw new \Europa\Reflection\Exception('A valid name for the author must be specified. None given.');
        }
        
        // set the type
        $this->name = trim($parts[0]);
    }
}