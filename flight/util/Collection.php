<?php
/**
 * Flight: An extensible micro-framework.
 *
 * @copyright   Copyright (c) 2011, Mike Cao <mike@mikecao.com>
 * @license     http://www.opensource.org/licenses/mit-license.php
 */

namespace flight\util;

/**
 * The Collection class allows you to access a set of data
 * using both array and object notation.
 */
class Collection implements \ArrayAccess, \Iterator, \Countable {
    /**
     * Collection data.
     *
     * @var array
     */
    private $data;

    /**
     * Constructor.
     *
     * @param array $data Initial data
     */
    public function __construct(array $data = array()) {
        $this->data = $data;
    }

    /**
     * Gets an item.
     *
     * @param string $key Key
     * @return mixed Value
     */
    public function __get($key) {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    /**
     * Set an item.
     *
     * @param string $key Key
     * @param mixed $value Value
     */
    public function __set($key, $value) {
        $this->data[$key] = $value;
    }

    /**
     * Checks if an item exists.
     *
     * @param string $key Key
     * @return bool Item status
     */
    public function __isset($key) {
        return isset($this->data[$key]);
    }

    /**
     * Removes an item.
     *
     * @param string $key Key
     */
    public function __unset($key) {
        unset($this->data[$key]);
    }

    /**
     * Gets an item at the offset.
     *
     * @param string $offset Offset
     * @return mixed Value
     */
    public function offsetGet($offset) {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }

    /**
     * Sets an item at the offset.
     *
     * @param string $offset Offset
     * @param mixed $value Value
     */
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->data[] = $value;
        }
        else {
            $this->data[$offset] = $value;
        }
    }

    /**
     * Checks if an item exists at the offset.
     *
     * @param string $offset Offset
     * @return bool Item status
     */
    public function offsetExists($offset) {
        return isset($this->data[$offset]);
    }

    /**
     * Removes an item at the offset.
     *
     * @param string $offset Offset
     */
    public function offsetUnset($offset) {
        unset($this->data[$offset]);
    }

    /**
     * Resets the collection.
     */
    public function rewind() {
        reset($this->data);
    }
 
    /**
     * Gets current collection item.
     *
     * @return mixed Value
     */ 
    public function current() {
        return current($this->data);
    }
 
    /**
     * Gets current collection key.
     *
     * @return mixed Value
     */ 
    public function key() {
        return key($this->data);
    }
 
    /**
     * Gets the next collection value.
     *
     * @return mixed Value
     */ 
    public function next() 
    {
        return next($this->data);
    }
 
    /**
     * Checks if the current collection key is valid.
     *
     * @return bool Key status
     */ 
    public function valid()
    {
        $key = key($this->data);
        return ($key !== NULL && $key !== FALSE);
    }

    /**
     * Gets the size of the collection.
     *
     * @return int Collection size
     */
    public function count() {
        return sizeof($this->data);
    }

    /**
     * Gets the item keys.
     *
     * @return array Collection keys
     */
    public function keys() {
        return array_keys($this->data);
    }

    /**
     * Gets the collection data.
     *
     * @return array Collection data
     */
    public function getData() {
        return $this->data;
    }

    /**
     * Sets the collection data.
     *
     * @param array $data New collection data
     */
    public function setData(array $data) {
        $this->data = $data;
    }

    /**
     * Removes all items from the collection.
     */
    public function clear() {
        $this->data = array();
    }
}
?>
