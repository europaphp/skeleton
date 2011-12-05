<?php

namespace Test\Fs;
use Europa\Fs\Directory;
use Europa\Fs\Directory\Exception as DirectoryException;
use Europa\Fs\File;
use Europa\Fs\File\Exception as FileException;
use Testes\Test\Test;

class FileAndDirectoryTest extends Test
{
    public function setUp()
    {
        if (!$this->testWritable()) {
            $this->assert(false, "Could not run tests because the directory {$dir} is not writable.");
        }
    }

    public function testFileCreateOpenAndDelete()
    {
        if (!$this->testWritable()) {
            return;
        }

        try {
            $file = File::create($this->getTestFile());
        } catch (FileException $e) {
            $this->assert(false, "The file could not be created with message: {$e->getMessage()}.");
            return;
        }
        
        try {
            $file = File::open($file->getPathname());
        } catch (FileException $e) {
            $this->assert(false, "The file could not be opened with message: {$e->getMessage()}.");
            return;
        }
        
        try {
            $file->delete();
        } catch (FileException $e) {
            $this->assert(false, "The file {$file->getPathname()} could not be deleted with message: {$e->getMessage()}.");
        }
    }
    
    public function testDirectoryCreateOpenAndDelete()
    {
        if (!$this->testWritable()) {
            return;
        }

        try {
            $file = Directory::create($this->getTestDir());
        } catch (DirectoryException $e) {
            $this->assert(false, "The directory could not be created with message: {$e->getMessage()}.");
            return;
        }
        
        try {
            $file = Directory::open($file->getPathname());
        } catch (DirectoryException $e) {
            $this->assert(false, "The directory could not be opened with message: {$e->getMessage()}.");
            return;
        }
        
        try {
            $file->delete();
        } catch (DirectoryException $e) {
            $this->assert(false, "The directory {$file->getPathname()} could not be deleted with message: {$e->getMessage()}.");
        }
    }

    private function testWritable()
    {
        return is_writable($this->getBaseTestDir());
    }

    private function getTestFile()
    {
        return $this->getBaseTestDir() . '/test.file';
    }

    private function getTestDir()
    {
        return $this->getBaseTestDir() . '/test.dir';
    }

    private function getBaseTestDir()
    {
        return realpath(dirname(__FILE__) . '/../../');
    }
}