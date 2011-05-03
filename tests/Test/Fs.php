<?php

namespace Test;
use Europa\Unit\Test\Test;
use Europa\Fs\File;
use Europa\Fs\Directory;

class Fs extends Test
{
    public function testFileCreateOpenAndDelete()
    {
        try {
            $file = File::create(dirname(__FILE__) . '/__test.gag');
        } catch (\Exeception $e) {
            $this->assert(false, 'The file could not be created.');
        }
        
        try {
            $file = File::open($file->getPathname());
        } catch (\Exception $e) {
            $this->assert(false, 'The file could not be opened. For some reason, it may have not been created, but no exception was thrown.');
        }
        
        try {
            $file->delete();
        } catch (\Exception $e) {
            $this->assert(false, 'The file could not be deleted. You will have to delete the test file: ' . $file->getPathname());
        }
    }
    
    public function testDirectoryCreateOpenAndDelete()
    {
        try {
            $file = Directory::create(dirname(__FILE__) . '/__test');
        } catch (\Exeception $e) {
            $this->assert(false, 'The directory could not be created.');
        }
        
        try {
            $file = Directory::open($file->getPathname());
        } catch (\Exception $e) {
            $this->assert(false, 'The directory could not be opened. For some reason, it may have not been created, but no exception was thrown.');
        }
        
        try {
            $file->delete();
        } catch (\Exception $e) {
            $this->assert(false, 'The directory could not be deleted. You will have to delete the test file: ' . $file->getPathname());
        }
    }
}