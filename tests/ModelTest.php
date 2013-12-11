<?php

class ModelTest extends PHPUnit_Framework_TestCase
{
    protected static $fixtures;

    public function setUp()
    {
        $this->m1 = new \Packman\Model(array('id' => 1, 'name' => 'hoge', 'age' => 20));
    }

    public function tearDown()
    {
        unset($this->m1);
    }

    public function test_iterator()
    {
        $expected = array(1, 'hoge', 20);
        $attrs = array();
        foreach($this->m1 as $attr) {
            array_push($attrs, $attr);
        }
        $this->assertSame($expected, $attrs);
    }

    public function test_offset_exists()
    {
        $this->assertTrue(isset($this->m1['id']));
    }

    public function test_offset_get()
    {
        $this->assertSame(1, $this->m1['id']);
    }

    public function test_offset_set()
    {
        $this->m1['name'] = 'piyo';
        $this->assertSame('piyo', $this->m1['name']);
    }

    public function test_offset_unset()
    {
        unset($this->m1['name']);
        $this->assertTrue(empty($this->m1['name']));
    }

    public function test_magic_isset()
    {
        $this->assertTrue(isset($this->m1->id));
    }

    public function test_magic_get()
    {
        $this->assertSame(1, $this->m1->id);
    }

    public function test_magic_set()
    {
        $this->m1->name = 'piyo';
        $this->assertSame('piyo', $this->m1->name);
    }

    public function test_magic_unset()
    {
        unset($this->m1->name);
        $this->assertTrue(empty($this->m1->name));
    }
}
