<?php

class Test_Mongo_Document extends Europa_Unit_Test
{
    public function setUp()
    {
        $this->bench = new Europa_Bench;
    }
    
    public function tearDown()
    {
        $obj = new TestDb_TestDoc;
        $obj->getCollection()->getDb()->drop();
    }
    
    public function testFill()
    {
        $obj = new TestDb_TestDoc;
        $obj->fill(array('test' => 'test'));
        return $obj->test === 'test';
    }
    
    public function testFillOnConstruction()
    {
        $obj = new TestDb_TestDoc(array('yomama' => 'issostupid', 'shefell' => 'offthefloor'));
        return $obj->yomama  === 'issostupid'
            && $obj->shefell === 'offthefloor';
    }
    
    public function testSetAndGet()
    {
        $obj = new TestDb_TestDoc;
        $obj->test = 'test';
        return $obj->test === 'test';
    }
    
    public function testIssetAndUnset()
    {
        $obj = new TestDb_TestDoc;
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
        $obj = new TestDb_TestDoc(array('test1' => 'value1', 'test2' => 'value2'));
        $arr = array();
        foreach ($obj as $name => $value) {
            $arr[$name] = $value;
        }
        return $arr['test1'] === 'value1'
            && $arr['test2'] === 'value2';
    }
    
    public function testArrayAccessSetAndGet()
    {
        $obj = new TestDb_TestDoc;
        $obj['test1'] = 'value1';
        $obj['test2'] = 'value2';
        return $obj['test1'] === 'value1'
            && $obj['test2'] === 'value2';
    }
    
    public function testArrayAccessIssetAndUnset()
    {
        $obj = new TestDb_TestDoc;
        $obj['test'] = 'test';
        
        $isset = isset($obj['test']);
        
        unset($obj['test']);
        
        return $isset && !isset($obj['test']);
    }
    
    public function testCount()
    {
        $obj = new TestDb_TestDoc(array('test1' => 'value1', 'test2' => 'value2'));
        return count($obj) === 2;
    }
    
    public function testEmbedDocumentSetting()
    {
        $obj = new TestDb_TestDoc;
        $obj->embed = array('test' => 'test');
        return $obj->embed instanceof TestDb_TestDoc_TestEmbed
            && $obj->embed->test === 'test';
    }
    
    public function testReferenceDocumentSetting()
    {
        $obj = new TestDb_TestDoc;
        $obj->reference = array('test' => 'test');
        return $obj->reference instanceof TestDb_TestReference
            && $obj->reference->test === 'test';
    }
    
    public function testEmbedCollectionSettingWithEmbeddedDocument()
    {
        $obj = new TestDb_TestDoc;
        $obj->embeds = array(
            array('test' => 'test1'),
            array('test' => 'test2')
        );
        return $obj->embeds instanceof Europa_Mongo_EmbeddedCollection
            && $obj->embeds[0] instanceof TestDb_TestDoc_TestEmbed
            && $obj->embeds[1] instanceof TestDb_TestDoc_TestEmbed
            && $obj->embeds[0]->test === 'test1'
            && $obj->embeds[1]->test === 'test2';
    }
    
    public function testEmbedCollectionAppendingWithEmbeddedDocument()
    {
        $obj = new TestDb_TestDoc;
        $obj->embeds[] = array('test' => 'test1');
        $obj->embeds[] = array('test' => 'test2');
        return $obj->embeds instanceof Europa_Mongo_EmbeddedCollection
            && $obj->embeds[0] instanceof TestDb_TestDoc_TestEmbed
            && $obj->embeds[1] instanceof TestDb_TestDoc_TestEmbed
            && $obj->embeds[0]->test === 'test1'
            && $obj->embeds[1]->test === 'test2';
    }
    
    public function testEmbedCollectionSettingWithEmbeddedReference()
    {
        $obj = new TestDb_TestDoc;
        $obj->references = array(
            array('test' => 'test1'),
            array('test' => 'test2')
        );
        return $obj->references    instanceof Europa_Mongo_EmbeddedCollection
            && $obj->references[0] instanceof TestDb_TestReference
            && $obj->references[1] instanceof TestDb_TestReference
            && $obj->references[0]->test === 'test1'
            && $obj->references[1]->test === 'test2';
    }
    
    public function testEmbedCollectionAppendingWithEmbeddedReference()
    {
        $obj = new TestDb_TestDoc;
        $obj->references[] = array('test' => 'test1');
        $obj->references[] = array('test' => 'test2');
        return $obj->references    instanceof Europa_Mongo_EmbeddedCollection
            && $obj->references[0] instanceof TestDb_TestReference
            && $obj->references[1] instanceof TestDb_TestReference
            && $obj->references[0]->test === 'test1'
            && $obj->references[1]->test === 'test2';
    }
    
    public function testNonExistentSingularRelationshipGetting()
    {
        $obj = new TestDb_TestDoc;
        return $obj->embed instanceof TestDb_TestDoc_TestEmbed;
    }
    
    public function testNonExistentMultipleRelationshipGetting()
    {
        $obj = new TestDb_TestDoc;
        return $obj->embeds instanceof Europa_Mongo_EmbeddedCollection;
    }
    
    public function testSingleLevelSave()
    {
        $obj = new TestDb_TestDoc;
        $obj->test = 'test1';
        $obj->save();
        return $obj->getCollection()->where('test', 'test1')->count() === 1;
    }
    
    public function testSingleLevelSaveWithOneEmbedded()
    {
        $obj = new TestDb_TestDoc;
        $obj->embed = array('oneEmbed' => true);
        $obj->save();
        return $obj->getCollection()->where('embed.oneEmbed', true)->count() === 1;
    }
    
    public function testMultiLevelSaveWithMultipleEmbedded()
    {
        $obj = new TestDb_TestDoc;
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
        $obj = new TestDb_TestDoc;
        $obj->load(array('test' => 'test1'));
        $obj->reference = array('test' => 'test2');
        $obj->save();
        
        $ref = $obj->getCollection()->getDb()->testReference->where('test', 'test2')->offsetGet(0);
        return $ref instanceof Europa_Mongo_Document
            && $ref->test === 'test2';
    }
    
    public function testMultiLevelSaveWithMultipleReferences()
    {
        $obj = new TestDb_TestDoc;
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

class TestDb_TestDoc extends Europa_Mongo_Document
{
    public function preConstruct()
    {
        $this->hasOne('embed', 'TestDb_TestDoc_TestEmbed');
        $this->hasOne('reference', 'TestDb_TestReference');
        $this->hasMany('embeds', 'TestDb_TestDoc_TestEmbed');
        $this->hasMany('references', 'TestDb_TestReference');
    }
}

class TestDb_TestDoc_TestEmbed extends Europa_Mongo_EmbeddedDocument
{
    
}

class TestDb_TestReference extends Europa_Mongo_Document
{
    
}