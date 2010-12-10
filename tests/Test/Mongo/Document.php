<?php

class Test_Mongo_Document extends Europa_Unit_Test
{
    public function setUp()
    {
        $this->bench = new Europa_Bench;
    }
    
    public function tearDown()
    {
        $obj = new TestDoc;
        $obj->getCollection()->getDb()->drop();
    }
    
    public function testFill()
    {
        $obj = new TestDoc;
        $obj->fill(array('test' => 'test'));
        return $obj->test === 'test';
    }
    
    public function testFillOnConstruction()
    {
        $obj = new TestDoc(array('yomama' => 'issostupid', 'shefell' => 'offthefloor'));
        return $obj->yomama  === 'issostupid'
            && $obj->shefell === 'offthefloor';
    }
    
    public function testSetAndGet()
    {
        $obj = new TestDoc;
        $obj->test = 'test';
        return $obj->test === 'test';
    }
    
    public function testIssetAndUnset()
    {
        $obj = new TestDoc;
        $obj->test = 'test';
        
        $unset = false;
        if (isset($obj->test)) {
            $unset = true;
            unset($obj->test);
        }
        
        return !isset($obj->test) && $unset;
    }
    
    public function testIteration()
    {
        $obj = new TestDoc(array('test1' => 'value1', 'test2' => 'value2'));
        $arr = array();
        foreach ($obj as $name => $value) {
            $arr[$name] = $value;
        }
        return $arr['test1'] === 'value1'
            && $arr['test2'] === 'value2';
    }
    
    public function testArrayAccessSetAndGet()
    {
        $obj = new TestDoc;
        $obj['test1'] = 'value1';
        $obj['test2'] = 'value2';
        return $obj['test1'] === 'value1'
            && $obj['test2'] === 'value2';
    }
    
    public function testArrayAccessIssetAndUnset()
    {
        $obj = new TestDoc;
        $obj['test'] = 'test';
        
        $isset = isset($obj['test']);
        
        unset($obj['test']);
        
        return $isset && !isset($obj['test']);
    }
    
    public function testCount()
    {
        $obj = new TestDoc(array('test1' => 'value1', 'test2' => 'value2'));
        return count($obj) === 2;
    }
    
    public function testEmbedDocumentSetting()
    {
        $obj = new TestDoc;
        $obj->embed = array('test' => 'test');
        return $obj->embed instanceof TestEmbed
            && $obj->embed->test === 'test';
    }
    
    public function testReferenceDocumentSetting()
    {
        $obj = new TestDoc;
        $obj->reference = array('test' => 'test');
        return $obj->reference instanceof TestReference
            && $obj->reference->test === 'test';
    }
    
    public function testEmbedCollectionSettingWithEmbeddedDocument()
    {
        $obj = new TestDoc;
        $obj->embeds = array(
            array('test' => 'test1'),
            array('test' => 'test2')
        );
        return $obj->embeds instanceof Europa_Mongo_EmbeddedCollection
            && $obj->embeds[0] instanceof TestEmbed
            && $obj->embeds[1] instanceof TestEmbed
            && $obj->embeds[0]->test === 'test1'
            && $obj->embeds[1]->test === 'test2';
    }
    
    public function testEmbedCollectionAppendingWithEmbeddedDocument()
    {
        $obj = new TestDoc;
        $obj->embeds[] = array('test' => 'test1');
        $obj->embeds[] = array('test' => 'test2');
        return $obj->embeds instanceof Europa_Mongo_EmbeddedCollection
            && $obj->embeds[0] instanceof TestEmbed
            && $obj->embeds[1] instanceof TestEmbed
            && $obj->embeds[0]->test === 'test1'
            && $obj->embeds[1]->test === 'test2';
    }
    
    public function testEmbedCollectionSettingWithEmbeddedReference()
    {
        $obj = new TestDoc;
        $obj->references = array(
            array('test' => 'test1'),
            array('test' => 'test2')
        );
        return $obj->references    instanceof Europa_Mongo_EmbeddedCollection
            && $obj->references[0] instanceof TestReference
            && $obj->references[1] instanceof TestReference
            && $obj->references[0]->test === 'test1'
            && $obj->references[1]->test === 'test2';
    }
    
    public function testEmbedCollectionAppendingWithEmbeddedReference()
    {
        $obj = new TestDoc;
        $obj->references[] = array('test' => 'test1');
        $obj->references[] = array('test' => 'test2');
        return $obj->references    instanceof Europa_Mongo_EmbeddedCollection
            && $obj->references[0] instanceof TestReference
            && $obj->references[1] instanceof TestReference
            && $obj->references[0]->test === 'test1'
            && $obj->references[1]->test === 'test2';
    }
    
    public function testNonExistentSingularRelationshipGetting()
    {
        $obj = new TestDoc;
        return $obj->embed instanceof TestEmbed;
    }
    
    public function testNonExistentMultipleRelationshipGetting()
    {
        $obj = new TestDoc;
        return $obj->embeds instanceof Europa_Mongo_EmbeddedCollection;
    }
    
    public function testSingleLevelSave()
    {
        $obj = new TestDoc;
        $obj->test = 'test1';
        $obj->save();
        return $obj->getCollection()->where('test', 'test1')->count() === 1;
    }
    
    public function testSingleLevelSaveWithOneEmbedded()
    {
        $obj = new TestDoc;
        $obj->embed = array('oneEmbed' => true);
        $obj->save();
        return $obj->getCollection()->where('embed.oneEmbed', true)->count() === 1;
    }
    
    public function testMultiLevelSaveWithMultipleEmbedded()
    {
        $obj = new TestDoc;
        $obj->embeds = array(
            array('multiEmbed' => true),
            array('multiEmbed' => true)
        );
        $obj->save();
        
        $coll = $obj->getCollection()->where('embeds.multiEmbed', true);
        return $coll[0]->embeds->count() === 2;
    }
    
    public function testMultiLevelSaveWithOneReference()
    {
        $obj = new TestDoc;
        $obj->load(array('test' => 'test1'));
        $obj->reference = array('test' => 'test2');
        $obj->save();
        
        $ref = $obj->getCollection()->getDb()->testReference->where('test', 'test2')->offsetGet(0);
        return $ref instanceof Europa_Mongo_Document
            && $ref->test === 'test2';
    }
    
    public function testMultiLevelSaveWithMultipleReferences()
    {
        $obj = new TestDoc;
        $obj->test = 'test1';
        $obj->load();
        $obj->references = array(
            array('test' => 'test3'),
            array('test' => 'test4')
        );
        $obj->save();
        
        return $obj->getCollection()->getDb()->testReference->where('test', array('$in' => array('test3', 'test4')))->count() === 2;
    }
}

class TestDoc extends Europa_Mongo_Document
{
    public function preConstruct()
    {
        $this->setDb('testDb');
        $this->setCollection('testDoc');
        
        $this->hasOne('embed', 'TestEmbed');
        $this->hasOne('reference', 'TestReference');
        $this->hasMany('embeds', 'TestEmbed');
        $this->hasMany('references', 'TestReference');
    }
}

class TestEmbed extends Europa_Mongo_EmbeddedDocument
{
    
}

class TestReference extends Europa_Mongo_Document
{
    public function preConstruct()
    {
        $this->setDb('testDb');
        $this->setCollection('testReference');
    }
}