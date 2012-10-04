<?php

namespace Test\All\Fs;
use Europa\Fs\Directory;
use Europa\Fs\File;
use Exception;
use Testes\Test\UnitAbstract;

class FileAndDirectoryTest extends UnitAbstract
{
    public function setUp()
    {
        if (!$this->testWritable()) {
            $this->assert(false, "Could not run tests because the directory {$this->getBaseTestDir()} is not writable.");
        }
    }

    public function testFileCreateOpenAndDelete()
    {
        if (!$this->testWritable()) {
            return;
        }

        try {
            $file = File::create($this->getTestFile());
        } catch (Exception $e) {
            $this->assert(false, "The file could not be created with message: {$e->getMessage()}.");
            return;
        }
        
        try {
            $file = File::open($file->getPathname());
        } catch (Exception $e) {
            $this->assert(false, "The file could not be opened with message: {$e->getMessage()}.");
            return;
        }
        
        try {
            $file->delete();
        } catch (Exception $e) {
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
        } catch (Exception $e) {
            $this->assert(false, "The directory could not be created with message: {$e->getMessage()}.");
            return;
        }
        
        try {
            $file = Directory::open($file->getPathname());
        } catch (Exception $e) {
            $this->assert(false, "The directory could not be opened with message: {$e->getMessage()}.");
            return;
        }
        
        try {
            $file->delete();
        } catch (Exception $e) {
            $this->assert(false, "The directory {$file->getPathname()} could not be deleted with message: {$e->getMessage()}.");
        }
    }

    private function testWritable()
    {
        return is_writable(sys_get_temp_dir());
    }

    private function getTestFile()
    {
        return sys_get_temp_dir() . '/test.file';
    }

    private function getTestDir()
    {
        return sys_get_temp_dir() . '/test.dir';
    }
}