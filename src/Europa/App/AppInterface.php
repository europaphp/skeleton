<?php

namespace Europa\App;

interface AppInterface
{
    /**
     * The action parameter.
     * 
     * @var string
     */
    const PARAM_ACTION = '_action';

    /**
     * The controller parameter.
     * 
     * @var string
     */
    const PARAM_CONTROLLER = '_controller';

    /**
     * The exception parameter.
     * 
     * @var string
     */
    const PARAM_EXCEPTION = '_exception';
    
    /**
     * Bootstraps the application.
     * 
     * @return App
     */
    public function bootstrap();

    /**
     * Runs the application.
     * 
     * @return App
     */
    public function run();
}