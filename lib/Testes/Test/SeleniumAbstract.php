<?php

namespace Testes\Test;

abstract class SeleniumAbstract extends UnitAbstract
{
    private $config = array(
        'browser' => '*firefox',
        'host'    => 'localhost',
        'port'    => 4444,
        'timeout' => 45,
        'url'     => null
    );
    
    private $sessionId;
    
    /**
     * Sets up and configures the driver.
     * 
     * @return \Testes\Test\Type\SeleniumAbstract
     */
    public function __construct(array $config = array())
    {
        $this->config = array_merge($this->config, $config);
    }
    
    /**
     * Ensures the test is completed.
     * 
     * @return void
     */
    public function __destruct()
    {
        if ($this->sessionId) {
            $this->testComplete();
        }
    }
    
    /**
     * Send a command to the Selenium RC server and returns the result.
     *
     * @param string $command The command to send.
     * @param array  $args    The arguments to pass
     * 
     * @return string
     */
    public function __call($command, array $args = array())
    {
        return $this->curl(
            'http://{$this->config['host']}:{$this->config['port']}/selenium-server/driver/',
            $args
        );
    }
    
    /**
     * Calls the specified URL.
     * 
     * @param string $url The URL to call.
     * 
     * @return \Testes\Test\Type\SeleniumAbstract
     */
    public function go($url)
    {
        if (!$this->sessionId) {
            $this->sessionId = $this->getNewBrowserSession(
                $this->config['browser'],
                $url
            );
            
            $this->setTimeout($this->config['timeout'] * 1000));
        }
        return $this;
    }
    
    /**
     * Executes the specified url with the provided arguments.
     * 
     * @param string $url  The url to execute.
     * @param array  $args The arguments to use.
     * 
     * @return array
     */
    private function curl($url, array $args = array())
    {
        
        $post = $this->curlFormatPost($command, $args);
        $curl = curl_init();
        
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded; charset=utf-8'
        ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
        
        return $this->curlExec($curl);;
    }
    
    /**
     * Executes the curl request.
     * 
     * @param curl $curl The curl object to execute.
     * 
     * @return array
     */
    private function curlExec($curl)
    {
        $data = curl_exec($curl);
        $info = curl_getinfo($curl);
        
        curl_close($curl);
        
        if (strstr($data, 'ERROR: ') === $data) {
            throw new \RuntimeException($data);
        }
        
        if (!$data) {
            throw new \RuntimeException('Curl error: ' . curl_error($curl));
        }
        
        if ($info['http_code'] !== 200) {
            throw new \RuntimeException(
                'The response from the Selenium RC server is invalid: ' . $data
            );
        }
        
        return $data;
    }
    
    /**
     * Formats the post data for the curl object.
     * 
     * @param string $command The command being run.
     * @param array  $args    The arguments to use.
     * 
     * @return string
     */
    private function curlFormatPost($command, array $args = array())
    {
        $post = sprintf('cmd=%s', urlencode($command));
        
        for ($i = 0; $i < count($args); $i++) {
            $argNum = strval($i + 1);
            if ($args[$i] == ' ') {
                $post .= sprintf('&%s=%s', $argNum, urlencode($args[$i]));
            } else {
                $post .= sprintf('&%s=%s', $argNum, urlencode(trim($args[$i])));
            }
        }
        
        if (isset($this->sessionId)) {
            $post .= sprintf('&%s=%s', 'sessionId', $this->sessionId);
        }
        
        return $post;
    }
}