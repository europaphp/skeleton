<?php

/**
 * Provides email validation according to RFC 3696.
 * 
 * Code adapted from: http://www.linuxjournal.com/article/9585.
 * 
 * @category Validation
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
namespace Europa\Validator
{
    class Email extends \Europa\Validator
    {
        /**
         * Checks to make sure the specified value is set.
         * 
         * @param mixed $email The email to validate.
         * 
         * @return void
         */
        public function validate($email)
        {
            if (!$email) {
                $this->pass();
                return $this;
            }
            
            $isValid = true;
            $atIndex = strrpos($email, '@');
            if (is_bool($atIndex) && !$atIndex) {
                $isValid = false;
            } else {
                $domain    = substr($email, $atIndex + 1);
                $local     = substr($email, 0, $atIndex);
                $localLen  = strlen($local);
                $domainLen = strlen($domain);
                if ($localLen < 1 || $localLen > 64) {
                    // local part length exceeded
                    $isValid = false;
                } elseif ($domainLen < 1 || $domainLen > 255) {
                    // domain part length exceeded
                    $isValid = false;
                } elseif ($local[0] == '.' || $local[$localLen - 1] == '.') {
                    // local part starts or ends with '.'
                    $isValid = false;
                } elseif (preg_match('/\\.\\./', $local)) {
                    // local part has two consecutive dots
                    $isValid = false;
                } elseif (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
                    // character not valid in domain part
                    $isValid = false;
                } elseif (preg_match('/\\.\\./', $domain)) {
                    // domain part has two consecutive dots
                    $isValid = false;
                } elseif (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace('\\\\', '', $local))) {
                    // character not valid in local part unless local part is quoted
                    if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\","",$local))) {
                        $isValid = false;
                    }
                }
                
                // check to make sure it's a valid registered email address
                if ($isValid && !(checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A'))) {
                    $isValid = false;
                }
            }
            
            if ($isValid) {
                $this->pass();
            } else {
                $this->fail();
            }
            return $this;
        }
    }
}