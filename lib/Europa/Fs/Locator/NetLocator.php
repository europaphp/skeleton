<?php

namespace Europa\Fs\Locator;
use Europa\Fs\File;

/**
 * Locates a resource on the internet. If it is found, it's contents is saved to the specified path.
 * 
 * @category Fs
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class NetLocator implements LocatorInterface
{
    /**
     * The URIs to call in search of a valid match.
     * 
     * @var array
     */
    private $uris = array();
    
    /**
     * Adds a URI to the list of URIs to search.
     * 
     * @param string $uri    The URI to add.
     * @param string $savePath The path to save the found items to.
     * 
     * @return \Europa\Fs\Locator\NetLocator
     */
    public function addUri($uri, $savePath)
    {
        $this->uris[$uri] = $savePath;
        return $this;
    }
    
    /**
     * Locates the specified resource from the given URLs, saves the contents and returns it's path. If no match is
     * found, false is returned.
     * 
     * @param string $file The file to locate.
     * 
     * @return string|false
     */
    public function locate($file)
    {
        $file = $this->formatFile($file);
        foreach ($this->uris as $uri => $savePath) {
            $uri = $this->formatUri($uri, $file);
            $out = $this->callUri($uri);
            if ($out !== false) {
                $found = $this->formatSavePath($savePath, $file);
                $found = File::overwrite($found);
                $found->setContents($out);
                return $found->getRealpath();
            }
        }
        return false;
    }
    
    /**
     * Calls the specified URI and returns the result.
     * 
     * @param string $uri The URI to call.
     * 
     * @return string|false
     */
    private function callUri($uri)
    {
        return file_get_contents($uri);
    }
    
    /**
     * Formats the specified file before locating.
     * 
     * @param string $file The file that is being located that we want to format.
     * 
     * @return string
     */
    private function formatFile($file)
    {
        $file = str_replace(array('/', '\\'), '/', $file);
        $file = trim($file, '/');
        return $file;
    }
    
    /**
     * The URI formatter.
     * 
     * @param string $uri  The URI to format.
     * @param string $file The file to for the URI.
     * 
     * @return string
     */
    private function formatUri($uri, $file)
    {
        $uri = str_replace(':file', $file, $uri);
        return $uri;
    }
    
    /**
     * The save to path formatter.
     * 
     * @param string $savePath The path to save to.
     * @param string $file     The file to save.
     * 
     * @return string
     */
    private function formatSavePath($savePath, $file)
    {
        $savePath = rtrim($savePath, '/\\');
        $savePath = str_replace(':file', $file, $savePath);
        return $savePath;
    }
}
