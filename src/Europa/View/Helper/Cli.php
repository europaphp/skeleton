<?php

namespace Europa\View\Helper;

/**
 * A helper for command line output.
 * 
 * @category Helpers
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Cli
{
    /**
     * English name color mapping to its color code.
     * 
     * @var array
     */
    private $colorMap = array(
        'bold'      => 1,
        'underline' => 4,
        'highlight' => 7,
        'red'       => 31,
        'green'     => 32,
        'yellow'    => 33,
        'blue'      => 34,
        'purple'    => 35,
        'cyan'      => 36,
        'white'     => 37,
        'red/white' => 41
    );
    
    /**
     * Outputs the specified text using the specified color.
     * 
     * @param string $text  The text to output.
     * @param int    $color The color to use.
     * 
     * @return string
     */
    public function color($text, $color = 31)
    {
        // map colors if found
        if (isset($this->colorMap[$color])) {
            $color = $this->colorMap[$color];
        }
        
        // format to console output and return
        return chr(27) . "[;{$color} m{$text}" . chr(27) . '[00m';
    }
}