<?php

class CollectionTest extends PHPUnit_Framework_TestCase
{
    protected static $fixtures;

    public static function setUpBeforeClass()
    {
        $fixtures = Fixture::load('models');
        self::$fixtures = $fixtures['array_of_hash'];
    }

    public static function tearDownAfterClass()
    {
        self::$fixtures = null;
    }

    public function setUp()
    {
        // models: array of text
        $this->text_models = array('hoge', 'fuge', 'piyo');
        $this->text_models_collection = new \Packman\Collection(array('models' => $this->text_models));

        // models: array of hash
        $this->array_models = self::$fixtures;
        $this->array_models_collection = new \Packman\Collection(array('models' => $this->array_models));
        $this->array_models_collection_with_index = new \Packman\Collection(array('index' => 'id', 'models' => $this->array_models));

        // models: array of object
        $this->object_models = array_map(function ($array_model) { return new \Packman\Model($array_model); }, self::$fixtures);
        $this->object_models_collection = new \Packman\Collection(array('models' => $this->object_models));
        $this->object_models_collection_with_index = new \Packman\Collection(array('index' => 'id', 'models' => $this->object_models));
    }

    public function tearDown()
    {
    }

    // ---
    // test case
    // ---
    
    public function test_when_no_id_and_no_models_index_should_be_null_and_models_should_be_an_empty_array()
    {
        $collection = new \Packman\Collection();
        $this->assertNull($collection->index());
        $this->assertEquals(array(), $collection->models());
    }

    public function test_when_no_id_and_no_models_add_text_should_work()
    {
        $collection = new \Packman\Collection();
        foreach($this->text_models as $text_model) { $collection->add($text_model); }
        $this->for_text_models_collection($collection);
    }

    public function test_when_no_id_and_no_models_add_array_should_work()
    {
        $collection = new \Packman\Collection();
        foreach($this->array_models as $array_model) { $collection->add($array_model); }
        $this->for_array_models_collection($collection);
    }

    public function test_when_no_id_and_no_models_add_model_object_should_work()
    {
        $collection = new \Packman\Collection();
        foreach($this->object_models as $object_model) { $collection->add($object_model); }
        $this->for_object_models_collection($collection);
    }

    public function test_text_models_collection()
    {
        $this->for_text_models_collection($this->text_models_collection);
    }

    public function test_array_models_collection()
    {
        $this->for_array_models_collection($this->array_models_collection);
    }

    public function test_array_models_collection_with_index()
    {
        $this->for_array_models_collection_with_index($this->array_models_collection_with_index);
    }

    public function test_object_models_collection()
    {
        $this->for_object_models_collection($this->object_models_collection);
    }

    public function test_object_models_collection_with_index()
    {
        $this->for_object_models_collection_with_index($this->object_models_collection_with_index);
    }

    // ---
    // index and key
    // ---

    public function test_when_original_is_pure_hash_collections_index_should_be_the_hash_key()
    {
        $originals = array('k1' => 1, 'k2' => 2, 'k3' => 3);
        $collection = new \Packman\Collection(array('models' => $originals));
        $this->assertSame(1, $collection->get('k1'));
    }

    public function test_add_with_key()
    {
        $originals = array('k1' => 1, 'k2' => 2, 'k3' => 3);
        $collection = new \Packman\Collection(array('models' => $originals));
        $collection->add(4, 'k4');
        $this->assertSame(4, $collection->get('k4'));
    }

    public function test_index_should_be_prior_to_key()
    {
        $originals = array(
            '001' => array('user_id' => '001', 'name' => 'hoge'),
            '002' => array('user_id' => '002', 'name' => 'fuge'),
            '003' => array('user_id' => '003', 'name' => 'piyo')
        );

        // if index and key given
        $collection = new \Packman\Collection(array('index' => 'user_id', 'models' => $originals));
        $collection->add(array('user_id' => '999', 'name' => 'piyo'), '004');
        // index should be prior to key
        $this->assertSame(array('user_id' => '999', 'name' => 'piyo'), $collection->get('999'));
        $this->assertNotSame(array('user_id' => '999', 'name' => 'piyo'), $collection->get('004'));
    }

    public function test_when_index_given_collections_index_should_be_models_index_value_even_if_original_hash_key_is_different()
    {
        $originals = array(
            '001' => array('user_id' => '001', 'name' => 'hoge'),
            '002' => array('user_id' => '002', 'name' => 'fuge'),
            // original hash key is different from model's index value
            '999' => array('user_id' => '003', 'name' => 'piyo')
        );

        // if index given
        $collection = new \Packman\Collection(array('index' => 'user_id', 'models' => $originals));

        // collection's index value should be models's index value. not original hash key
        $this->assertSame(array('user_id' => '003', 'name' => 'piyo'), $collection->get('003'));
    }

    // ---
    // filter
    // ---

    public function test_filter_should_retain_keys_when_no_index()
    {
        $originals = array('k1' => 1, 'k2' => 2, 'k3' => 3);
        $collection = new \Packman\Collection(array('models' => $originals));
        $filtered = $collection->filter(function($v) { return $v >= 2; });
        $this->assertSame(array('k2' => 2, 'k3' => 3), $filtered->models());
    }

    // ---
    // filter_by
    // ---

    public function test_filter_by_should_retain_keys_when_no_index()
    {
        $originals = array(
            '001' => array('user_id' => '001', 'name' => 'hoge'),
            '002' => array('user_id' => '002', 'name' => 'fuge'),
            '003' => array('user_id' => '003', 'name' => 'piyo')
        );
        $collection = new \Packman\Collection(array('models' => $originals));
        $filtered = $collection->filter_by('user_id', '>=', '002');
        $this->assertSame(array('002', '003'), array_keys($filtered->models()));
    }

    // ---
    // sort
    // ---

    public function test_sort_should_retain_keys_when_no_index()
    {
        $originals = array('k1' => 1, 'k2A' => 2, 'k2B' => 2, 'k3' => 3);
        $collection = new \Packman\Collection(array('models' => $originals));
        $sorted = $collection->sort(function($a, $b) { 
			if ($a === $b) return 1;
		    return ($a > $b) ? -1 : 1;
        });
        $this->assertSame(array('k3' => 3, 'k2A' => 2, 'k2B' => 2, 'k1' => 1), $sorted->models());
    }

    // ---
    // sort_by
    // ---

    public function test_sort_by_should_retain_keys_when_no_index()
    {
        // TODO
        // now, sort_by doesn't retaion order of same value
        //
        // $originals = array('k1' => 1, 'k2A' => 2, 'k2B' => 2, 'k3' => 3);
        // $collection = new \Packman\Collection(array('models' => $originals));
        // $sorted = $collection->sort_by(function($v) { return -$v; });
        // $this->assertSame(array('k3' => 3, 'k2A' => 2, 'k2B' => 2, 'k1' => 1), $sorted->models());
    }

    public function test_sort_by_should_work_when_models_collection()
    {
        $collection = $this->object_models_collection_with_index;
        $collection->sort_by(function ($model) { return $model->age; });
        $this->assertSame(array('003', '002', '001'), array_keys($collection->models()));
        $this->assertSame(array('003', '002', '001'), $collection->pluck('id'));
        $this->assertSame(array('piyo', 'fuge', 'hoge'), $collection->pluck('name'));
    }

    // ---
    // slice
    // ---

    public function test_slice_should_retain_keys_when_no_index()
    {
        $originals = array('k1' => 1, 'k2A' => 2, 'k2B' => 2, 'k3' => 3);
        $collection = new \Packman\Collection(array('models' => $originals));
        $sliced = $collection->slice(0, 2);
        $this->assertSame(array('k1' => 1, 'k2A' => 2), $sliced->models());
    }
    
    public function test_slice_should_retain_keys_when_models_collection_with_index()
    {
        $collection = $this->object_models_collection_with_index;
        $sliced = $collection->slice(0, 2);
        $this->assertSame(array('001', '002'), array_keys($sliced->models()));
        $this->assertSame(array('001', '002'), $sliced->pluck('id'));
    }


    // ---
    // helper assertions
    // ---

    protected function for_text_models_collection($collection)
    {
        $this->assertEquals('hoge', $collection->get(0));
        $this->assertEquals('fuge', $collection->get(1));
        $this->assertEquals('piyo', $collection->get(2));
        $this->assertEquals('hoge', $collection->first());
        $this->assertEquals('piyo', $collection->last());
        $this->assertEquals(1, $collection->filter(function($model) { return $model === 'hoge'; })->count());
    }

    protected function for_array_models_collection($collection)
    {
        $this->assertSame(array('id' => '001', 'name' => 'hoge', 'sex' => 'man', 'age' => '30'), $collection->get(0));
        $this->assertSame(array('id' => '002', 'name' => 'fuge', 'sex' => 'woman', 'age' => '25'), $collection->get(1));
        $this->assertSame(array('id' => '003', 'name' => 'piyo', 'sex' => 'woman', 'age' => '20'), $collection->get(2));
        $this->assertSame(array('id' => '001', 'name' => 'hoge', 'sex' => 'man', 'age' => '30'), $collection->first());
        $this->assertSame(array('id' => '003', 'name' => 'piyo', 'sex' => 'woman', 'age' => '20'), $collection->last());
        $this->assertEquals(1, $collection->filter(function($model) { return $model['sex'] === 'man'; })->count());
        $this->assertEquals(array('hoge', 'fuge', 'piyo'), $collection->pluck('name'));

        $hoge = $collection->get_by('name', 'hoge');
        $this->assertSame('001', $hoge['id']);
    }

    protected function for_array_models_collection_with_index($collection)
    {
        $this->assertSame(array('id' => '001', 'name' => 'hoge', 'sex' => 'man', 'age' => '30'),   $collection->get('001'));
        $this->assertSame(array('id' => '002', 'name' => 'fuge', 'sex' => 'woman', 'age' => '25'), $collection->get('002'));
        $this->assertSame(array('id' => '003', 'name' => 'piyo', 'sex' => 'woman', 'age' => '20'), $collection->get('003'));
        $this->assertSame(array('id' => '001', 'name' => 'hoge', 'sex' => 'man', 'age' => '30'),   $collection->first());
        $this->assertSame(array('id' => '003', 'name' => 'piyo', 'sex' => 'woman', 'age' => '20'), $collection->last());
        $this->assertEquals(1, $collection->filter(function($model) { return $model['sex'] === 'man'; })->count());
        $this->assertSame(array('hoge', 'fuge', 'piyo'), $collection->pluck('name'));

        $hoge = $collection->get_by('name', 'hoge');
        $this->assertSame('001', $hoge['id']);
    }

    protected function for_object_models_collection($collection)
    {
        $this->assertSame('001', $collection->get(0)->id);
        $this->assertSame('002', $collection->get(1)->id);
        $this->assertSame('003', $collection->get(2)->id);
        $this->assertSame('001', $collection->first()->id);
        $this->assertSame('003', $collection->last()->id);
        $this->assertEquals(1, $collection->filter(function($model) { return $model->sex === 'man'; })->count());
        $this->assertEquals(array('hoge', 'fuge', 'piyo'), $collection->pluck('name'));

        $hoge = $collection->get_by('name', 'hoge');
        $this->assertSame('001', $hoge->id);

        foreach($collection as $k => $model) {
            $this->assertContains($k, array(0, 1, 2));
            $this->assertContains($model->id, array('001', '002', '003'));
        }

        $this->to_json_case($collection);
    }

    protected function for_object_models_collection_with_index($collection)
    {
        // get
        $this->assertSame('001', $collection->get('001')->id);
        $this->assertSame('002', $collection->get('002')->id);
        $this->assertSame('003', $collection->get('003')->id);

        // get_by
        $hoge = $collection->get_by('name', 'hoge');
        $this->assertSame('001', $hoge->id);

        // first, last
        $this->assertSame('001', $collection->first()->id);
        $this->assertSame('003', $collection->last()->id);

        // filter
        $this->assertSame(1, $collection->filter(function($model) { return $model->sex === 'man'; })->count());

        // filter_by
        $this->assertSame('002', $collection->filter_by('name', '=', 'fuge')->first()->id);
        $this->assertSame('001', $collection->filter_by('age', '>', 25)->first()->id);
        $this->assertSame('003', $collection->filter_by('age', '<', 25)->first()->id);
        $this->assertSame('002', $collection->filter_by('age', '>=', 25)->last()->id);
        $this->assertSame('002', $collection->filter_by('age', '<=', 25)->first()->id);

        // pluck
        $this->assertSame(array('hoge', 'fuge', 'piyo'), $collection->pluck('name'));

        // foreach
        foreach($collection as $k => $model) {
            $this->assertContains($k, array('001', '002', '003'));
            $this->assertContains($model->id, array('001', '002', '003'));
        }

        // to_json
        $this->to_json_case($collection);
    }

    protected function to_json_case($collection)
    {
        $expected = '[{"id":"001","name":"hoge","sex":"man","age":"30"},{"id":"002","name":"fuge","sex":"woman","age":"25"},{"id":"003","name":"piyo","sex":"woman","age":"20"}]';
        $this->assertSame($expected, $collection->to_json());

        $expected = '[{"id":"001","name":"hoge"},{"id":"002","name":"fuge"},{"id":"003","name":"piyo"}]';
        $this->assertSame($expected, $collection->to_json(array('only' => array('id', 'name'))));
    }
}

