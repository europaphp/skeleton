<?php

namespace Europa\Response;
use Countable;
use IteratorAggregate;

interface HttpInterface extends Countable, IteratorAggregate, ResponseInterface
{
    const CONTENT_TYPE = 'Content-Type';

    const LOCATION = 'Location';

    const HTML = 'text/html';
    
    const JSON = 'application/json';

    const JSONP = 'application/javascript';

    const XML = 'text/xml';
    
    const OK = 200;

    const NOT_FOUND = 404;

    const INTERNAL_SERVER_ERROR = 500;
    
    public function setHeader($name, $value);
        
    public function getHeader($name);
    
    public function hasHeader($name);
    
    public function removeHeader($name);
    
    public function setHeaders($headers);
    
    public function getHeaders();
    
    public function removeHeaders();

    public function setContentType($type);

    public function getContentType();

    public function setContentTypeFromView($view);

    public function setViewContentType($view, $type);

    public function getViewContentType($view);

    public function setLocation($location);

    public function getLocation();
    
    public function setStatus($status);
    
    public function getStatus();
}