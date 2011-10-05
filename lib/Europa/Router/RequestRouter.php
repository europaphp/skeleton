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
     * @var array
     */
    private $resolvers = array();
    
    /**
     * The subject to match.
     * 
     * @var string
     */
    private $subject;
    
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
     * Adds a resolver to the router.
     * 
     * @param \Europa\Router\Resolver\ResolverInterface $resolver The resolver to add.
     * 
     * @return \Europa\Router\RequestRouter
     */
    public function addResolver(ResolverInterface $resolver)
    {
        $this->resolvers[] = $resolver;
        return $this;
    }
    
    /**
     * Routes the specified request. If a subject is specified it is used instead of the default Europa request URI.
     * 
     * @param RequestInterface $request The request to route.
     * 
     * @return bool
     */
    public function route(RequestInterface $request)
    {
        // figure out the subject to match
        $subject = $this->subject;
        
        // by default if no subject is specified, it uses the default string representation of the request
        if (!$subject) {
            $subject = $request->__toString();
        }
        
        // uses the first successful resolver
        foreach ($this->resolvers as $resolver) {
            // will either be an array or false
            $params = $resolver->query($subject);
            if ($params !== false) {
                // if the request receives anything but an array or object, it rejects the parameters
                $request->setParams($params);
                return true;
            }
        }
        
        return false;
    }
}
