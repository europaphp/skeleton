<?php

class Europa_Controller_Exception extends Europa_Exception
{
    const
        /**
         * The exception/error code that identifies and exception with a
         * controller not being found.
         */
        CONTROLLER_NOT_FOUND = 1,

        /**
         * The exception/error code that identifies and exception with a action
         * not being found.
         */
        ACTION_NOT_FOUND = 2,

        /**
         * Fired when a required parameter inside an action is not defined in
         * the request.
         */
        REQUIRED_PARAMETER_NOT_DEFINED = 3;
}