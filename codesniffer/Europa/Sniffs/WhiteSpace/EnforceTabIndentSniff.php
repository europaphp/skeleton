<?php
/**
 * Europa_Sniffs_WhiteSpace_EnforceTabIndentSniff.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   CVS: $Id: EnforceTabIndentation.php 267906 2008-10-28 04:43:57Z squiz $
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * Europa_Sniffs_WhiteSpace_EnforceTabIndentSniff.
 *
 * Throws errors if tabs are used for indentation.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   Release: 1.2.2
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class Europa_Sniffs_WhiteSpace_EnforceTabIndentSniff implements PHP_CodeSniffer_Sniff
{

    /**
     * A list of tokenizers this sniff supports.
     *
     * @var array
     */
    public $supportedTokenizers = array(
                                   'PHP',
                                   'JS',
                                   'CSS',
                                  );


    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(T_WHITESPACE);

    }//end register()


    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile All the tokens found in the document.
     * @param int                  $stackPtr  The position of the current token in
     *                                        the stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // Make sure this is whitespace used for indentation.
        $line = $tokens[$stackPtr]['line'];
        if ($stackPtr > 0 && $tokens[($stackPtr - 1)]['line'] === $line) {
            return;
        }

		$hasSpace            = strpos($tokens[$stackPtr]['content'], ' ') !== false;
		$hasMoreThanOneSpace = strpos($tokens[$stackPtr]['content'], '  ') !== false;
		$hasTab              = strpos($tokens[$stackPtr]['content'], "\t") !== false;
		$hasTabAndSpace      = strpos($tokens[$stackPtr]['content'], "\t ") !== false;

		// primary indentation MUST be tabs, however, spaces may need to be used to line up code blocks
        if ($hasMoreThanOneSpace && !$hasTabAndSpace) {
            $error = 'Tabs must be used to indent lines; spaces are not allowed.';
            $phpcsFile->addError($error, $stackPtr);
        }
    }
}