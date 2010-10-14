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
 * @version   CVS: $Id: EuropaCodingStandard.php 269131 2008-11-17 05:04:07Z squiz $
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
     * The Europa standard uses some generic sniffs.
     *
     * @return array
     */
    public function getIncludedSniffs()
    {
        return array(
            'Generic/Sniffs/Classes/DuplicateClassNameSniff.php',
            'Generic/Sniffs/Commenting/TodoSniff.php',
            'Generic/Sniffs/ControlStructures/InlineControlStructureSniff.php',
            'Generic/Sniffs/Files/LineEndingsSniff.php',
            'Generic/Sniffs/Formatting/DisallowMultipleStatementsSniff.php',
            'Generic/Sniffs/Formatting/SpaceAfterCastSniff.php',
            'Generic/Sniffs/Functions/CallTimePassByReferenceSniff.php',
            'Generic/Sniffs/Functions/OpeningFunctionBraceBsdAllmanSniff.php',
            'Generic/Sniffs/NamingConventions/ConstructorNameSniff.php',
            'Generic/Sniffs/NamingConventions/UpperCaseConstantNameSniff.php',
            'Generic/Sniffs/PHP/DisallowShortOpenTagSniff.php',
            'Generic/Sniffs/PHP/LowerCaseConstantSniff.php',
            'Generic/Sniffs/PHP/ForbiddenFunctionsSniff.php',
            'Generic/Sniffs/WhiteSpace/DisallowTabIndentSniff.php',
            'Generic/Sniffs/WhiteSpace/ScopeIndentSniff.php',
            'PEAR/Sniffs/Classes/ClassDeclarationSniff.php',
            'PEAR/Sniffs/ControlStructures/ControlSignatureSniff.php',
            'PEAR/Sniffs/ControlStructures/MultiLineConditionSniff.php',
            'PEAR/Sniffs/Files/IncludingFileSniff.php',
            'PEAR/Sniffs/Formatting/MultiLineAssignmentSniff.php',
            'PEAR/Sniffs/Functions/FunctionCallArgumentSpacingSniff.php',
            'PEAR/Sniffs/Functions/FunctionCallSignatureSniff.php',
            'PEAR/Sniffs/Functions/FunctionDeclarationSniff.php',
            'PEAR/Sniffs/Functions/ValidDefaultValueSniff.php',
            'PEAR/Sniffs/NamingConventions/ValidClassNameSniff.php',
            'PEAR/Sniffs/NamingConventions/ValidFunctionNameSniff.php',
            'PEAR/Sniffs/NamingConventions/ValidVariableNameSniff.php',
            'PEAR/Sniffs/WhiteSpace/ObjectOperatorIndentSniff.php',
            'PEAR/Sniffs/WhiteSpace/ScopeClosingBraceSniff.php'
        );

    }//end getIncludedSniffs()


}//end class
?>
