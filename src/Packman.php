<?php
namespace Packman;

class Collection implements \IteratorAggregate, \Countable, \ArrayAccess
{
    protected $index = null;
    protected $models = array();

    public function __construct($options = array())
    {
        $this->index = isset($options['index']) ? $options['index'] : null;
        if (isset($options['models'])) $this->set_models($options['models']);
    }

    public function index()
    {
        return $this->index;
    }

    public function models()
    {
        return $this->models;
    }

    public function set_models(array $models)
    {
        foreach($models as $key => $model) {
            $this->add($model, $key); 
        }
    }

    public function add($model, $key = null)
    {
        // if index is null and key is null, model push to models
        if (is_null($this->index) && is_null($key)) { $this->models[] = $model; return; }

        // if index property exist, use `index value of model` as `key of models`
        if ($this->index) $key = $model[$this->index];

        $this->models[$key] = $model;
    }

    public function get($index)
    {
        if (array_key_exists($index, $this->models)) {
            return $this->models[$index];
        } else {
            return null;
        }
    }

    public function keys()
    {
        return array_keys($this->models);
    }

    public function first()
    {
		return count($this->models) > 0 ? reset($this->models) : null;
    }

    public function last()
    {
		return count($this->models) > 0 ? end($this->models) : null;
    }

    public function pluck($key)
    {
        $datas = array();

        foreach($this as $model) {
            array_push($datas, $model[$key]);
        }

        return $datas;
    }

    public function get_by($k, $v)
    {
        foreach($this->models as $model) {
            $bool = call_user_func(function($model) use ($k, $v) { return $model[$k] == $v;}, $model);
            if ($bool) return $model; 
        }
    }

    public function filter($callback)
    {
        $model_array = array_filter($this->models, $callback);
        return new static(array('index' => $this->index, 'models' => $model_array));
    }

    public function filter_by($k, $operator, $v)
    {
        $callback = function($model) use ($k, $operator, $v) {
            switch ($operator) {
                case '=':
                    return $model[$k] == $v;
                    break;
                case '>':
                    return $model[$k] > $v;
                    break;
                case '<':
                    return $model[$k] < $v;
                    break;
                case '>=':
                    return $model[$k] >= $v;
                    break;
                case '<=':
                    return $model[$k] <= $v;
                    break;
                default:
                    throw new \Exception('invalid operator!');
                    break;
            }
        };

        $model_array = array_filter($this->models, $callback);
        return new static(array('index' => $this->index, 'models' => $model_array));
    }

    public function sort($callback)
    {
        uasort($this->models, $callback);
        return $this;
    }

    // original: laravel
    public function sort_by($callback)
    {
		$results = array();

		foreach ($this->models as $key => $value) {
			$results[$key] = $callback($value);
		}

		asort($results);

		foreach (array_keys($results) as $key) {
			$results[$key] = $this->models[$key];
		}

		$this->models = $results;

		return $this;
    }

    // NOTICE: retain keys
	public function slice($offset, $length = null, $preserveKeys = true)
	{
		$model_array = array_slice($this->models, $offset, $length, $preserveKeys);
        return new static(array('index' => $this->index, 'models' => $model_array));
	}

    public function get_random()
    {
        $index = array_rand($this->models);
        return $this->get($index);
    }

    public function to_json($options = array())
    {
        $array_models = array();

        foreach ($this->models as $model) {
            $array_models[] = $model->to_array($options);
        }

        return json_encode($array_models, 0);
    }

    /**
     * implement \IteratorAggregate
     */
    public function getIterator() {
        return new \ArrayIterator($this->models);
    }

    /**
     * implement \ArrayAccess
     */
    public function offsetExists($offset) {
        return isset($this->models[$offset]);
    }

    /**
     * implement \ArrayAccess
     */
    public function offsetGet($offset) {
        return $this->models[$offset];
    }
    
    /**
     * implement \ArrayAccess
     */
    public function offsetSet($offset, $value) {
        $this->models[$offset] = $value;
    }

    /**
     * implement \ArrayAccess
     */
    public function offsetUnset($offset) {
        unset($this->models[$offset]);
    }

    /**
     * implement \Countable
     */
    public function count()
    {
        return count($this->models);
    }
}

class Model implements \IteratorAggregate, \ArrayAccess
{
    protected $attrs;

    public function __construct($attrs = null)
    {
        $this->attrs = $attrs;
    }

    public function attrs()
    {
        return $this->attrs;
    }

    public function to_array($options = array())
    {
        if (isset($options['only'])) {
            $keys = (array)$options['only'];
            $array = array();

            foreach($keys as $key) {
                $array[$key] = $this->attrs[$key];
            }

            return $array;
        } else {
            return $this->attrs;
        }
    }

    public function to_json($options = array())
    {
        $data = $this->to_array($options);
        return json_encode($data, 0);
    }

    /**
     * implement \IteratorAggregate
     */
    public function getIterator() {
        return new \ArrayIterator($this->attrs);
    }

    /**
     * implement \ArrayAccess
     */
    public function offsetExists($offset) {
        // call __isset
        return isset($this->$offset);
    }

    /**
     * implement \ArrayAccess
     */
    public function offsetGet($offset) {
        // call __get
        return $this->$offset;
    }
    
    /**
     * implement \ArrayAccess
     */
    public function offsetSet($offset, $v) {
        // call __set
        return $this->$offset = $v;
    }

    /**
     * implement \ArrayAccess
     */
    public function offsetUnset($offset) {
        // call __unset
        unset($this->$offset);
    }

    public function __isset($k)
    {
        return isset($this->attrs[$k]);
    }

    public function __get($k)
    {
        return $this->attrs[$k];
    }

    public function __set($k, $v)
    {
        $this->attrs[$k] = $v;
    }

    public function __unset($k)
    {
        unset($this->attrs[$k]);
    }
}
