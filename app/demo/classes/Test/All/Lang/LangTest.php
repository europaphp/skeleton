<?php

namespace Test\All\Lang;
use Europa\Lang\Adapter\Ini;
use Europa\Lang\Lang;
use Exception;
use Testes\Test\UnitAbstract;

class LangTest extends UnitAbstract
{
    public function basic()
    {
        $lang = new Lang;
        $lang->setting = 'some set value';
        $lang->add(['added1' => 'added value 1', 'added2' => 'added value 2']);
        $lang->addAdapter(new Ini(__DIR__ . '/../../Provider/Lang/test.ini'));

        $this->assert($lang->getting === 'some value', 'Value should have been returned.');
        $this->assert($lang->calling1('value') === 'some value', 'Value should have been formatted using positional args.');
        $this->assert($lang->calling2(['value' => 'value']) === 'some value', 'Value should have been formatted using named args.');
        $this->assert($lang->setting === 'some set value', 'Value should have been set.');
        $this->assert($lang->added1 === 'added value 1', 'Value should have been added.');
        $this->assert($lang->added2() === 'added value 2', 'Value should have been added.');
    }

    public function unsetting()
    {
        $lang = new Lang;
        $lang->some = 'value';
        unset($lang->some);

        $this->assert(!$lang->some, 'Value should have been unset.');
    }

    public function badIniFile()
    {
        try {
            new Ini('somebadfile');
            $this->assert(false, 'Exception should have been thrown for bad ini file.');
        } catch (Exception $e) {}
    }

    public function badIniAdapter()
    {
        $lang = new Lang;

        try {
            $lang->addAdapter('somebadadpater');
            $this->assert(false, 'An exception should have been thrown for the non-callable adapter.');
        } catch (Exception $e) {}
    }

    public function nullValues()
    {
        $lang = new Lang;

        $this->assert($lang->somevalue() === null, 'Value should be null.');
        $this->assert($lang->somevalue === null, 'Value should be null.');
    }
}