<?php

namespace Test\All\Config;
use Europa\Config\Adapter\Ini;
use Europa\Config\Config;
use Exception;
use Testes\Test\UnitAbstract;

class ConfigTest extends UnitAbstract
{
    public function constructor()
    {
        $config = new Config(
            ['test1' => false],
            ['test1' => true],
            ['test2' => true]
        );
        
        $this->assert($config->test1 && $config->test2 && $config->test3, 'Configuration should have all 3 test values.');
    }

    public function accessingMagicAndArrayAccess()
    {
        $config = new Config([
            'some.nested.value' => true
        ]);

        $this->assert($config->some->nested->value, 'Using "some->nested->value" does not work.');
        $this->assert($config['some.nested.value'], 'Using "some.nested.value" does not work.');

        unset($config->some->nested->value);

        $this->assert(!isset($config->some->nested->value), 'Option "some->nested->value" should have been unset.');

        unset($config['some.nested']);

        $this->assert(!isset($config['some.nested']), 'Option "some.nested" should have been unset.');
    }

    public function iteration()
    {
        $config = new Config([
            'some.values' => [true, true]
        ]);

        foreach ($config['some.values'] as $index => $value) {
            $this->assert(is_numeric($index), 'Index should be numeric.');
            $this->assert($value, 'Value should have evaluated to true.');
        }

        $this->assert(count($config['some.values']) === 2, 'Option "some.values" should have a count of 2.');
    }

    public function exporting()
    {
        $config = new Config([
            'some.test.array' => ['some' => 'value']
        ]);

        $compare = [
            'some' => [
                'test' => [
                    'array' => [
                        'some' => 'value'
                    ]
                ]
            ]
        ];

        $this->assert($config->export() === $compare, 'Arrays do not match.');
    }

    public function readonly()
    {
        $config = new Config;
        $config->readonly();

        try {
            $config->test = true;
            $this->assert(false, 'Exception should have been thrown indicating that the configuration is read only.');
        } catch (Exception $e) {}

        $config->readonly(false);

        try {
            $config->test = true;
        } catch (Exception $e) {
            $this->assert(false, 'Exception was thrown indication readonly, however, configuration should have been editable.');
        }
    }

    public function nestedPartNotConfigObject()
    {
        $config = new Config;
        $config['some.nested.value'] = true;

        try {
            $config['some.nested.value.shoudnotgethere'];
            $this->assert(false, 'Option "some.nested.value" should not return an object when accessing "some.nested.value.shouldnotgethere".');
        } catch (Exception $e) {}
    }

    public function references()
    {
        $config = new Config([
            'referencer' => '=referencing:{referencee}',
            'referencee' => 'somevalue'
        ]);

        $this->assert($config->referencer === 'referencing:somevalue', 'Option "referencee" was not referenced within "referencer".');
    }

    public function castingReferences()
    {
        $config = new Config([
            'float'      => 1.1,
            'referencer' => '={float}'
        ]);

        $this->assert($config->referencer === 1.1, 'Value referencing the float should result as a float.');
    }

    public function castingMultipleNonStringReferences()
    {
        $config = new Config([
            'int1'       => 1,
            'int2'       => 2,
            'referencer' => '={int1}.{int2}'
        ]);

        $this->assert($config->referencer === 1.2);
    }

    public function castingMultipleReferencesContainintAString()
    {
        $config = new Config([
            'float'      => 1.1,
            'string'     => 'somestring',
            'referencer' => '={string}_{float}'
        ]);

        $this->assert($config->referencer === 'somestring_1.1');
    }

    public function adapter()
    {
        $config = new Config;
        $config->import(new Ini(__DIR__ . '/../../Provider/Config/test.ini'));

        $this->assert($config->values->value1, 'First value should have been parsed.');
        $this->assert($config['values.value2'], 'Second value should have been parse.d');
    }

    public function badIniFile()
    {
        try {
            new Ini('somebadfile');
            $this->assert(false, 'Exception should have been thrown for bad ini file.');
        } catch (Exception $e) {}
    }
}