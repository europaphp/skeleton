<?php

namespace Europa\Version;

class SemVer implements VersionInterface
{
  const WILDCARD = '*';

  private $major;

  private $minor;

  private $patch;

  private $preRelease;

  private $build;

  private $parts = [
    'major',
    'minor',
    'patch',
    'preRelease',
    'build'
  ];

  public function __construct($version = null)
  {
    $this->set($version);
  }

  public function __toString()
  {
    $version = $this->major;

    if ($this->minor !== self::WILDCARD || $this->patch !== self::WILDCARD || $this->preRelease !== self::WILDCARD || $this->build !== self::WILDCARD) {
      $version .= '.' . $this->minor;
    }

    if ($this->patch !== self::WILDCARD || $this->preRelease !== self::WILDCARD || $this->build !== self::WILDCARD) {
      $version .= '.' . $this->patch;
    }

    if ($this->preRelease !== self::WILDCARD || $this->build !== self::WILDCARD) {
      $version .= '-' . $this->preRelease;
    }

    if ($this->build !== self::WILDCARD) {
      $version .= '+' . $this->build;
    }

    return $version;
  }

  public function major()
  {
    return $this->major;
  }

  public function minor()
  {
    return $this->minor;
  }

  public function patch()
  {
    return $this->patch;
  }

  public function preRelease()
  {
    return $this->preRelease;
  }

  public function build()
  {
    return $this->build;
  }

  public function set($version)
  {
    preg_match('/([0-9*]*)\.?([0-9*]*)\.?([0-9*]*)-?([0-9a-zA-Z-.*]*)\+?([0-9a-zA-Z-.*]*)/', $version, $versions);

    foreach ($this->parts as $index => $part) {
      $this->$part = $versions[$index + 1] === ''
        ? self::WILDCARD
        : $versions[$index + 1];
    }

    return $this;
  }

  public function is($version)
  {
    if (!$version instanceof self) {
      $version = new self($version);
    }

    foreach ($this->parts as $part) {
      if (!$this->isPart($this->$part, $version->$part())) {
        return false;
      }
    }

    return true;
  }

  private function isPart($part1, $part2)
  {
    return
      $part1 === self::WILDCARD ||
      $part2 === self::WILDCARD ||
      $part1 === $part2;
  }
}