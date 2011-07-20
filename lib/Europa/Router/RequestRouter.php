<?php

namespace Europa\Router;
use Europa\Request\RequestInterface;
use Europa\Router\Resolver\ResolverInterface;

/**
 * Default request router implementation.
 * 
 * @category Router
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class RequestRouter implements RequestRouterInterface
{
    /**
     * The router resolver to use for resolving routes.
     * 
     * @var ResolverInterface
     */
    private $resolver;
    
    /**
     * The subject to match.
     * 
     * @var string
     */
    private $subject;
    
    /**
     * Sets up the router.
     * 
     * @param ResolverInterface $resolver The resolver to use for resolving routes for the request.
     * 
     * @return RequestRouter
     */
    public function __construct(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }
    
    /**
     * Sets the subject.
     * 
     * @param string $subject The subject to match.
     * 
     * @return RequestRouter
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }
    
    /**
     * Returns the subject.
     * 
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }
    
    /**
     * Routes the specified request. If a subject is specified it is used instead of the default Europa request URI.
     * 
     * @param RequestInterface  $request  The request to route.
     * @param ResolverInterface $resolver The resolver to use for matching.
     * 
     * @return void
     */
    public function route(RequestInterface $request)
    {
        // figure out the subject to match
        $subject = $this->subject;
        
        // by default if no subject is specified, it uses the Europa request URI from the request's URI object
        if (!$subject) {
            $subject = $request->getUri()->getRequest();
        }
        
        // will either be an array or false
        $params = $this->resolver->query($subject);
        
        // if the request receives anything but an array or object, it rejects the parameters
        $request->setParams($params);
    }
}
