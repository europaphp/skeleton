<?php

namespace Test\All\Config;
use Europa\Config\Config;
use Exception;
use Testes\Test\UnitAbstract;

class ConfigTest extends UnitAbstract
{
    private $path;

    public function setUp()
    {
        $this->path = __DIR__ . '/../../Provider/Config/';
    }

    public function constructor()
    {
        $config = new Config(
            ['test1' => false],
            ['test1' => true],
            ['test2' => true]
        );
        
        $this->assert($config->test1 && $config->test2, 'Configuration should have overridden the first array.');
    }

    public function accessingMagicAndArrayAccess()
    {
        $config = new Config([
            'some' => ['nested' => ['value' => true]]
        ]);

        $this->assert($config->some->nested->value, 'Using "some->nested->value" does not work.');
        $this->assert($config['some']['nested']['value'], 'Using "some.nested.value" does not work.');

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
        $original = [
            'some.test.array' => ['some' => 'value']
        ];

        $config = new Config($original);

        $this->assert($config->export() === $original, 'Arrays do not match.');
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
        $config = new Config(['some.nested.value' => true]);
        $this->assert(!$config['some.nested.value.shoudnotgethere'], 'When accessing a nested level that does not exist, it should not return anything.');
    }

    public function references()
    {
        $config = new Config([
            'referencer' => 'referencing:{referencee}',
            'referencee' => 'somevalue'
        ]);

        $this->assert($config->referencer === 'referencing:somevalue', 'Option "referencee" was not referenced within "referencer".');
    }

    public function castingReferences()
    {
        $config = new Config([
            'float'      => 1.1,
            'referencer' => '{float}'
        ]);

        $this->assert($config->referencer === '1.1', 'Value referencing the float should result as a float.');
    }

    public function castingMultipleNonStringReferences()
    {
        $config = new Config([
            'int1'       => 1,
            'int2'       => 2,
            'referencer' => '{int1}.{int2}'
        ]);

        $this->assert($config->referencer === '1.2');
    }

    public function parentAccess()
    {
        $config = new Config([
            'value'  => 'test',
            'nested' => [
                'value'  => 'test{_parent.value}',
                'nested' => [
                    'value' => 'test{_parent._parent.value}{_root.value}'
                ]
            ]
        ]);

        $this->assert($config->nested->nested->value === 'testtesttest');
    }

    public function castingMultipleReferencesContainintAString()
    {
        $config = new Config([
            'float'      => 1.1,
            'string'     => 'somestring',
            'referencer' => '{$this->string}_{$this->float}'
        ]);

        $this->assert($config->referencer === 'somestring_1.1');
    }

    public function jsonFile()
    {
        $config = new Config($this->path . 'test.json');

        $this->assert($config->test, 'JSON file not parsed.');
    }

    public function ymlFile()
    {
        $config = new Config($this->path . 'test.yml');

        $this->assert($config->test, 'YAML .yml file not parsed.');
    }

    public function yamlFile()
    {
        $config = new Config($this->path . 'test.yaml');

        $this->assert($config->test, 'YAML .yaml file not parsed.');
    }
}