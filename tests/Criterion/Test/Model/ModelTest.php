<?php

namespace Criterion\Test\Model;

class ModelTest extends \Criterion\Test\TestCase
{

    public function testInit()
    {
        $model = new \Criterion\Model();
        $this->assertTrue($model instanceof \Criterion\Model);
        $this->assertTrue(is_object($model->id));
    }

    public function testSetAndGet()
    {
        $model = new \Criterion\Model();
        $model->testing = 'test';
        $this->assertEquals('test', $model->testing);
    }

    public function testGetNull()
    {
        $model = new \Criterion\Model();
        $this->assertEquals(null, $model->testing);
    }

    public function testIsset()
    {
        $model = new \Criterion\Model();
        $model->testing = 'test';
        $this->assertTrue(isset($model->testing));
        $this->assertFalse(isset($model->nope));
    }

    public function testToString()
    {
        $model = new \Criterion\Model();
        $this->assertEquals($model->id, (string) $model);
    }

    public function testSaveNew()
    {
        $model = new \Criterion\Model();
        $model->testing = 'test';
        $this->assertTrue($model->save());
        $this->assertTrue($model->exists);
    }

    public function testSaveExisting()
    {
        // Create it first
        $model = new \Criterion\Model();
        $model->testing = 'test';
        $this->assertTrue($model->save());
        $this->assertTrue($model->exists);

        // Then create the second model so we can edit it
        $existing = new \Criterion\Model($model->id);
        $this->assertTrue($existing->exists);
        $existing->testing = 'test2';
        $this->assertTrue($existing->save());
    }

    public function testExisting()
    {
        // Create it first
        $app = new \Criterion\Application();
        $document['testing'] = 'test';
        $app->db->tests->save($document);

        // Then create the second model so we can edit it
        $existing = new \Criterion\Model(null, $document);
        $this->assertTrue($existing->exists);
        $existing->testing = 'test2';
        $this->assertTrue($existing->save());
    }

    public function testDelete()
    {
        $model = new \Criterion\Model();
        $model->testing = 'test';
        $this->assertTrue($model->save());
        $this->assertTrue($model->exists);
        $this->assertTrue($model->delete());
        $this->assertFalse($model->exists);
    }

    public function testDeleteFail()
    {
        $model = new \Criterion\Model();
        $this->assertFalse($model->delete());
    }
}
