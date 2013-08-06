<?php
namespace Criterion;

class Model
{
    public $collection = null;
    public $data = array();
    public $id = null;
    public $exists = false;
    public $db = null;
    public $app = null;

    public function __construct($query = null, $existing = false)
    {
        $this->app = new \Criterion\Application();
        if (! $this->collection) {
            $this->collection = strtolower(get_called_class());
        }

        $this->db = $this->app->db->{$this->collection};

        if ($existing && !$query && isset($existing['_id'])) {
            $this->exists = true;
            $this->id = $existing['_id'];
            $this->data = $existing;
        } else {
            if (! is_array($query)) {
                if (! is_object($query)) {
                    try {
                        $query = new \MongoId($query);
                    } catch (\MongoException $e) {
                        // leave as _id
                    }
                }

                $query = array(
                    '_id' => $query
                );
            }

            $document = $this->db->findOne($query);
            if ($document) {
                $this->exists = true;
                $this->id = $document['_id'];
                $this->data = $document;
            } else {
                $this->id = new \MongoId();
                $this->data['_id'] = $this->id;

                // Build a default data set for this model
                if (method_exists($this, 'defaultData')) {
                    $this->defaultData($query);
                }

            }
        }
    }

    public function __get($key)
    {
        if (! isset($this->data[$key])) {
            return null;
        }

        return $this->data[$key];
    }

    public function __isset($key)
    {
        return isset($this->data[$key]);
    }

    public function __set($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function __toString()
    {
        return (string) $this->id;
    }

    public function delete()
    {
        if ($this->exists) {
            $delete = $this->db->remove(array(
                '_id' => $this->id
            ));

            $this->exists = false;

            return (bool) $delete['ok'];
        }

        return false;
    }

    public function save()
    {
        if ($this->id && ! isset($this->data['_id'])) {
            $this->data['_id'] = $this->id;
        }
        $save = $this->db->save($this->data);

        $this->exists = (bool) $save['ok'];
        return (bool) $save['ok'];
    }
}
