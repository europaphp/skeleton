<?php

/**
 * Europa Coding Standard.
 *
 * PHP version 5
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

if (class_exists('PHP_CodeSniffer_Standards_CodingStandard', true) === false) {
	throw new PHP_CodeSniffer_Exception('Class PHP_CodeSniffer_Standards_CodingStandard not found');
}

/**
 * Europa Coding Standard.
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
class PHP_CodeSniffer_Standards_Europa_EuropaCodingStandard extends PHP_CodeSniffer_Standards_CodingStandard
{
	/**
	 * Return a list of external sniffs to include with this standard.
	 *
	 * The Europa standards uses a mixture of sniffs.
	 *
	 * @return array
	 */
	public function getIncludedSniffs()
	{
		return array(
			'Generic/Sniffs/Functions/OpeningFunctionBraceBsdAllmanSniff.php',
			'Generic/Sniffs/PHP/DisallowShortOpenTagSniff.php',
			'PEAR/Sniffs/Classes/ClassDeclarationSniff.php',
			'PEAR/Sniffs/ControlStructures/ControlSignatureSniff.php',
			'PEAR/Sniffs/Files/LineEndingsSniff.php',
			'PEAR/Sniffs/Functions/FunctionCallArgumentSpacingSniff.php',
			'PEAR/Sniffs/Functions/ValidDefaultValueSniff.php',
			'PEAR/Sniffs/WhiteSpace/ScopeClosingBraceSniff.php',
			'Squiz/Sniffs/Functions/GlobalFunctionSniff.php',
			'Zend/Sniffs/Debug/CodeAnalyzerSniff.php',
			'Zend/Sniffs/Files/ClosingTagSniff.php',
			'Zend/Sniffs/NamingConventions/ValidVariableNameSniff.php'
		);
	}
}