<?php

namespace Criterion;

class Model
{
    /**
     * The ID of the current model object
     * @var \MongoId
     */
    public $id = null;

    /**
     * Does this object already exist?
     * @var boolean
     */
    public $exists = false;

    /**
     * Have we just created this object?
     * @var boolean
     */
    public $created = false;

    /**
     * The array used by __set() and __get()
     * @var array
     */
    protected $modelData = [];

    /**
     * Used to store the original find query response.
     * It is used when calling save(), and archive()
     * @var array
     */
    protected $modelDataOriginal = [];

    /**
     * What MongoCollection should we run queries on?
     * @var string
     */
    protected $collection = null;


    /**
     * MongoCollection object
     * @var object
     */
    protected $mongo = null;


    public function __construct($id = null, $data = false)
    {
        // Get the mongo collection, and assign it to $this->mongo
        $this->getMongoCollection();

        // If false is passed as the ID, then we dont want to do any ID checking
        if ($id !== false) {
            // Get the MongoID from the ID provided
            $this->getMongoid($id);

            if ($id) {

                if ($data) {
                    $this->modelData = $data;
                } else {
                    $this->modelData = $this->mongo->findOne(
                        [
                            '_id' => $this->id
                        ]
                    );
                }

                if ($this->modelData) {
                    $this->exists = true;
                    $this->modelDataOriginal = $this->modelData;
                }
            }

            // If nothing exists, then we create a default data array
            if (! $this->exists) {
                $this->modelData = [
                    '_id' => $this->id
                ];

                $this->created = true;
            }
        }

        return $this;
    }

    /**
     * Find from MongoDB by params
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @param  array  $params What params should we use to find this document?
     * @return object
     */
    public static function find(array $params)
    {
        $results = self::findAll($params, 1);

        if (! $results) {
            $class = $this->getChildModel();
            return new $class(false);
        }

        return reset($results);
    }

    /**
     * Return a count from a cursor
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @param  array $params
     * @return integer
     */
    public static function count($params)
    {
        $class = $this->getChildModel();
        $model = new $class(false);

        return $model->mongo->find($params)->count();
    }

    /**
     * Returns a an array from distinct
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @param  string $field
     * @param  array $query
     * @return array
     */
    public static function distinct($field, $query = [])
    {
        $class = $this->getChildModel();
        $model = new $class(false);

        return $model->mongo->distinct($field, $query);
    }

    /**
     * Find all depending on params, return an array of \Model objects
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @param  array $params
     * @param  int $limit
     * @param  array $sort
     * @param  boolean $response Should we return with getReponse() or not?
     * @return object|array
     */
    public static function findAll(array $params = array(), $limit = false, $skip = false, $sort = false)
    {
        // Initialise a Model instance, so we can get the collection etc
        $class = $this->getChildModel();
        $model = new $class(false);

        $cursor = $model->mongo->find($params);
        if ($limit) {
            $cursor->limit($limit);
        }

        if ($sort) {
            $cursor->sort($sort);
        }

        if ($skip) {
            $cursor->skip($skip);
        }

        $results = [];
        if ($cursor->count() > 0) {
            foreach ($cursor as $result) {
                $results[] = new $class($result['_id'], $result);
            }
        }

        return $results;
    }

    /**
     * Does this object exist
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @return bool
     */
    public function exists()
    {
        return $this->exists;
    }

    /**
     * Has this just been created?
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @return bool
     */
    public function created()
    {
        return $this->created;
    }

    /**
     * Incriment a key by a value
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @param  string  $key   Which document key should we use?
     * @param  integer $value The value to increment by
     * @return bool
     */
    public function increment($key, $value = 1)
    {
        $increment = $this->mongo->update([
            '_id' => $this->id
        ], [
            '$inc' => [
                $key => $value
            ]
        ]);

        return (bool) $increment['ok'];
    }

    /**
     * Save the current object to MongoDB. If this
     * If this already exists in MongoDB, then archive the current
     * data before saving the new data over it.
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @return bool
     */
    public function save()
    {
        // Archive this before we update
        if ($this->exists()) {
            $this->archive();
        }

        if (method_exists($this, 'beforeSave')) {
            $this->beforeSave();
        }

        $save = $this->mongo->update(
            [
                '_id' => $this->id
            ],
            $this->modelData,
            [
                'upsert' => true
            ]
        );

        $this->exists = (bool) $save['ok'];
        $this->created = ! (bool) $save['updatedExisting'];
        return $this->exists;
    }

    /**
     * Delete the current object from MongoDB. Archive
     * before doing so.
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @return bool
     */
    public function delete()
    {
        $this->archive('DELETE');

        $delete = $this->mongo->remove(
            [
                '_id' => $this->id
            ]
        );

        if ($delete['ok']) {
            $this->created = false;
            $this->exists = false;
        }

        return (bool) $delete['ok'];
    }

    /**
     * Magic Method for setting object data
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @param  string $key
     * @param  string|array|interger $value
     */
    public function __set($key, $value)
    {
        if (empty($value) && ! empty($this->$key)) {
            return false;
        }

        $this->modelData[$key] = $value;
    }

    /**
     * Magic Method for checking if object data is set
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @param  string  $key
     * @return boolean
     */
    public function __isset($key)
    {
        return isset($this->modelData[$key]);
    }

    /**
     * Magic Method for getting object data
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @param  string $key
     * @return string|array|integer
     */
    public function __get($key)
    {
        return array_key_exists($key, $this->modelData) ? $this->modelData[$key] : null;
    }

    /**
     * Magic Method for outputing object as a string
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @return string
     */
    public function __toString()
    {
        return (string) $this->id;
    }

    /**
     * Returns the name of the child model
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @return string
     */
    protected function getChildModel()
    {
        return get_called_class();
    }

    /**
     * Get the MongoCollection object. If $this->collection has
     * not been set, then we try and work it out from the calling
     * model
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @return object The MongoCollection object
     */
    protected function getMongoCollection()
    {
        if (! $this->collection) {
            $this->collection = strtolower(str_replace('Model\\', null, $this->getChildModel()));
        }

        $this->mongo = \Criterion\Application::getApp()['mongo']->{$this->collection};
    }

    /**
     * Attempt to work out the MongoID from a string|object
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @param  object|string $id
     * @return MonogId
     */
    protected function getMongoId($id)
    {
        // We have already worked the mongo id out
        if ($this->id) {
            return $this->id;
        }

        // If no id has been provided, then we generate a random one
        if (! $id) {
            $this->id =  new \MongoId();
        }

        if (is_object($id)) {
            $this->id = $id;
        }

        try {
            $this->id = new \MongoId($id);
        } catch (\Exception $e) {
            $this->id = $id;
        }

        return $this->id;
    }
}
