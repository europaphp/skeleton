<?php

namespace Europa\Reflection\DocTag;
use UnexpectedValueException;

class AuthorTag extends GenericTag
{
  private $name;

  private $email;

  public function setName($name)
  {
    $this->name = $name;
    return $this;
  }

  public function getName()
  {
    return $this->name;
  }

  public function setEmail($email)
  {
    $this->email = $email;
    return $this;
  }

  public function getEmail()
  {
    return $this->email;
  }

  public function parseValue($value)
  {
    // split in to tag/author name parts
    $parts = preg_replace('/\s+/', ' ', $value);
    $parts = preg_split('/\s+/', $value, 2);

    // require a name
    if (!isset($parts[0])) {
      throw new UnexpectedValueException('A valid name for the author must be specified.');
    }

    // require an email address
    if (!isset($parts[1])) {
      throw new UnexpectedValueException('A valid email address for the author must be specified.');
    }

    // set the name
    $this->name = trim($parts[0]);

    // set the email
    $this->email = trim($parts[1], '<>');
  }

  public function compileValue()
  {
    return $this->name . ' <' . $this->email . '>';
  }
}