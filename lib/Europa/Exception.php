<?php

/**
 * Contains the Europa_Exception class.
 *
 * PHP Version 5
 *
 * @category   Exceptions
 * @package    Europa
 * @subpackage Exception
 * @author     Trey Shugart <treshugart@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD
 * @link       http://europaphp.org/
 */

/**
 * Provides a general set of defaults for exception handling and output.
 *
 * @category   Exceptions
 * @package    Europa
 * @subpackage Exception
 * @author     Trey Shugart <treshugart@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD
 * @link       http://europaphp.org/
 */
class Europa_Exception extends Exception
{
    const
        /**
         * The event that gets fired before the exception message is displayed.
         * 
         * @var EVENT_BEFORE_OUTPUT
         */
        EVENT_BEFORE_OUTPUT = 'Europa_Exception.beforeOutput';



    /**
     * Constructs the Exception class and supplies a default exception
     * message/output.
     * 
     * It triggers an event before the exception is output so we can hook into 
     * this for whatever the case may be.
     *
     * @param string $message The exception message. If not set, this defaults 
     *                        to 'An unknown exception was thrown'.
     * @param int    $code    The exception code. If not set, this defaults to 
     *                        0.
     *
     * @return Europa_Exception
     */
    public function __construct($message = 'An unknown exception was thrown.', $code = 0)
    {
        // construct the Exception class
        parent::__construct($message, $code);

        /*
         * Triggers bound events for Europa_Exception.beforeOutput. Could be a custom
         * dispatch call, view object, etc. In order to prevent 
         * Europa_Exception::__toString() from executing the user must call either 
         * die or exit within one of the event handlers.
         */
        Europa_Event::trigger(self::EVENT_BEFORE_OUTPUT, array('exception' => $this));
    }

    /**
     * Outputs the error message along with the exception details and call-stack.
     * 
     * A good default error message is provided without configuration/extending,
     * etc. The call-stack is displayed in chronological order - the first being
     * the first call, etc.
     *
     * @return string
     */
    public function __toString()
    {
        $str = '
            <style type="text/css">
                body {
                    font-family : Arial, Tahoma, Verdana, Helvetica, Sans-Serif;
                    font-size   : 0.8em;
                    color       : #444;
                }
                ul {
                	
                }
                ul li {
                    list-style-type : none;
                }
                .file {
                    color : #333;
                }
                .class {
                    color : green;
                }
                .method {
                    color : red;
                }
                .line {
                    color : red;
                }
                .arg {
                    color      : blue;
                    font-style : italic;
                }
                .args {
                    font-family : courier new;
                    color       : #666;
                    padding     : 0px;
                }
                .args ul {
                    padding : 5px 20px;
                }
                .light {
                    color : #ccc;
                }
                .small {
                    font-size : 0.8em;
                }
            </style>
            
            <h1>Europa Exception</h1>
            
            <p>' . $this->getMessage() . '</p>
            
            <h2>Details</h2>
            
            <table style="border-collapse: collapse; border: 1px solid #ccc;">
		';
        
        foreach ($this as $k => $v) {
            if ($v === $this->getMessage()) {
                continue;
            }

            $str .= '
                <tr>
                    <td style="font-weight: bold; padding: 2px 4px; border: 1px solid #ddd; vertical-align: top; text-align: right;">' . $k . '</td>
                    <td style=" padding: 2px 4px; border: 1px solid #ddd;">' . $v . '</td>
                </tr>
			';
        }

        $str .= '
            </table>
            
            <h2>Call Stack</h2>
            <ol>
		';

        foreach ($this->getTrace() as $trace) {
            if (!isset($trace['file'])) {
                continue;
            }

            $args  = array();
            $class = isset($trace['class'])
                   ? '<span class="class">' . $trace['class'] . $trace['type'] . '</span>'
                   : '';

            $traceArgs = isset($trace['args']) ? $trace['args'] : array();
	    
            foreach ($traceArgs as $argKey => $argVal) {
                $args[] = '<span class="arg">' . ucfirst(gettype($argVal)) . '</span>';
            }

            $str .= '<li>'
            .  '<h3 class="file">' . $trace['file'] . ' : <span class="line">' . $trace['line'] . '</span></h3>'
            .  '<p>' . $class . '<span class="method">' . $trace['function'] . '(' . implode(', ', $args) . ')</span></p>'
            .  $this->_getArgList($traceArgs)
            .  '</li>';
        }

        $str .= '
			</ol>
		';

        return $str;
    }

    /**
     * The default exception handler that is defined after Europa_Controller is 
     * included.
     * 
     * This allows easy extending of Europa_Exception so you can implement
     * custom exceptions and handle them in different ways before they are output 
     * while automating the calling of __toString.
     *
     * @param object $e An exception of any kind. As long as the __toString method is
     *                  implemented, then this will work.
     *
     * @return null
     */
    static public function handle($e)
    {
        echo $e;

        exit;
    }

    /**
     * Triggers an exception.
     * 
     * Provides a way to trigger an exception-like error where exceptions can't be 
     * thrown. Same as using Europa_Exception::handle(new Europa_Exception($message, $code)).
     *
     * @param string $message The message to send to the exception.
     * @param int    $code    The exception code.
     *
     * @return null
     */
    static public function trigger($message = 'An unknown error has occured.', $code = 0)
    {
        self::handle(new self($message, $code));
    }



    /**
     * Returns a formatted string of arugments from the passed array of arguments.
     * 
     * @param array $arr The array of arguments to format.
     * 
     * @return string
     */
    private function _getArgList($arr)
    {
        if (!is_array($arr) || count($arr) === 0) {
            return null;
        }

        $str = '<ul class="args">';

        foreach ($arr as $v) {
            $value = $v;
            $len   = null;

            if (is_scalar($value)) {
                $value = (string) $value;
                $len   = is_string($v)
                ? '(' . strlen($value) . ')'
                : '';
            } else {
                $value = $this->_getArgList($v);
                $len   = '(' . count($v) . ')';
            }

            $str .= '<li>'
            .  '<em class="small">' . ucfirst(gettype($v)) . $len . '</em> ' . $value
            .  '<li>';
        }

        $str .= '</ul>';

        return $str;
    }
}