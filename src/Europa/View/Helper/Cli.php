<?php

namespace Europa\View\Helper;

class Cli
{
  private $colorMap = array(
    'bold'    => 1,
    'underline' => 4,
    'highlight' => 7,
    'red'     => 31,
    'green'   => 32,
    'yellow'  => 33,
    'blue'    => 34,
    'purple'  => 35,
    'cyan'    => 36,
    'white'   => 37,
    'red/white' => 41
  );

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