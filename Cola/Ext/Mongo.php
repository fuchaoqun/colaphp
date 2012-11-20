<?php
Class Cola_Com_Mongo
{
    protected $_mongo;
    protected $_db;

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct($config = array())
    {
        $config = (array)$config + array('server' => 'mongodb://localhost:27017', 'options' => array('connect' => true));

        extract($config);

        $this->_mongo = new Mongo($server, $options);

        if (isset($database)) {
            $this->_db = $this->db($database);
        }

        if (isset($user) && isset($password)) $this->auth($user, $password);
    }

    /**
     * Mongo
     *
     * @return Mongo
     */
    public function mongo()
    {
        return $this->_mongo;
    }

    /**
     * Select Database
     *
     * @param string $db
     * @return MongoDB
     */
    public function db($db = null)
    {
        if ($db) {
            return $this->_mongo->selectDB($db);
        }

        return $this->_db;
    }

    /**
     * Authenticate
     *
     * @param string $user
     * @param string $password
     */
    public function auth($user, $password)
    {
        $result = $this->_db->authenticate($user, $password);

        if (1 == $result['ok']) {
            return true;
        }

        throw new Cola_Exception('Mongo Auth Failed: bad user or password.');
    }

    /**
     * Select Collection
     *
     * @param string $collection
     * @return MogoCollection
     */
    public function collection($collection)
    {
        return $this->_db->selectCollection($collection);
    }

    /**
     * Find and return query result array.
     *
     * Pass the query and options as array objects (this is more convenient than the standard
     * Mongo API especially when caching)
     *
     * $options may contain:
     *   fields - the fields to retrieve
     *   sort - the criteria to sort by
     *   skip - skip number
     *   limit - limit number
     *   cursor - just return the result cursor.
     *
     * @param string $collection
     * @param array $query
     * @param array $options
     * @return mixed
     **/
    public function find($collection, $query = array(), $options = array())
    {
        $options += array('fields' => array(), 'sort' => array(), 'skip' => 0, 'limit' => 0, 'cursor' => false, 'tailable' => false);
        extract($options);

        $collection = $this->collection($collection);
        $cur = $collection->find($query, $fields);

        if ($sort) $cur->sort($sort);
        if ($skip) $cur->skip($skip);
        if ($limit) $cur->limit($limit);
        if ($tailable) $cur->tailable($tailable);

        if ($cursor) return $cur;

        return iterator_to_array($cur);
    }

    /**
     * Find one row
     *
     * @param string $collection
     * @param array $query
     * @param array $fields
     * @return array
     */
    public function findOne($collection, $query = array(), $fields = array())
    {
        $collection = $this->collection($collection);
        return $collection->findOne($query, $fields);
    }

    /**
     * Count the number of objects matching a query in a collection (or all objects)
     *
     * @param string $collection
     * @param array $query
     * @return integer
     **/
    public function count($collection, array $query = array()) {
        $res = $this->collection($collection);

        if ($query) {
            $res = $res->find($query);
        }

        return $res->count();
    }

    /**
     * Save a Mongo object -- if an exist mongo object, just update
     *
     * @param string $collection
     * @param array $data
     * @return boolean
     **/
    public function save($collection, $data) {
        return $this->collection($collection)->save($data);
    }

    /**
     * Insert a Mongo object
     *
     * @param string $collection
     * @param array $data
     * @return boolean
     **/
    public function insert($collection,$data, $options = array()) {
        return $this->collection($collection)->insert($data, $options);
    }

    /**
     * Update a Mongo object
     *
     * @param string $collection
     * @param array $query
     * @param array $data
     * @param array $options
     * @return mixed
     */
    public function update($collection, $query, $data, $options = array())
    {
        return $this->collection($collection)->update($query, $data, $options);
    }

    /**
     * Remove a Mongo object
     *
     * @param string $collection
     * @param array $query
     * @param array $options
     * @return mixed
     */
    public function remove($collection, $query, $options = array())
    {
        return $this->collection($collection)->remove($query, $options);
    }

    /**
     * Wrapper of findAndModfiy command:
     *
     * Options:
     * query	 a filter for the query,default is	{}
     * sort	     if multiple docs match, choose the first one in the specified sort order as the object to manipulate,default is {}
     * remove	 set to a true to remove the object before returning
     * update	 a modifier array
     * new	     set to true if you want to return the modified object rather than the original. Ignored for remove.
     * fields	 see Retrieving a Subset of Fields (1.5.0+)	 default is All fields.
     * upsert	 create object if it doesn't exist.
     *
     * @param string $collection
     * @param string $options
     * @return void
     */
    public function findAndModify($collection, $options = array()) {
        $result = $this->_db->command(array('findAndModify' => $collection) + $options);
        return $result['ok'] ? $result['value'] : $result;
    }

    public function autoIncrementId($domain, $collection = 'autoIncrementIds', $db = null)
    {
        $result = $this->db($db)->command(array(
            'findAndModify' => $collection,
            'query' => array('_id' => $domain),
            'update' => array('$inc' => array('val' => 1)),
            'new' => true,
            'upsert' => true
        ));

        if ($result['ok'] && $id = intval($result['value']['val'])) {
            return $id;
        }

        throw new Cola_Exception('Cola_Com_Mongo: gen auto increment id failed');
    }

    /**
     * MongoId
     *
     * @param string $id
     * @return MongoId
     */
    public static function id($id = null)
    {
        return new MongoId($id);
    }

    /**
     * MongoTimestamp
     *
     * @param int $sec
     * @param int $inc
     * @return MongoTimestamp
     */
    public static function Timestamp($sec = null, $inc = 0)
    {
        if (empty($sec)) $sec = time();
        return new MongoTimestamp($sec, $inc);
    }

    /**
     * GridFS
     *
     * @return MongoGridFS
     */
    public function gridFS($prefix = 'fs')
    {
        return $this->_db->getGridFS($prefix);
    }

    /**
     * Last error
     *
     * @return array
     */
    public function error()
    {
        return $this->db->lastError();
    }
}