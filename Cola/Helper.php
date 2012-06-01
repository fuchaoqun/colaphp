<?php
/**
 *
 */

class Cola_Helper
{
    /**
     * Dynamic get helper
     *
     * @param string $key
     */
    public function __get($key)
    {
        $className = 'Cola_Helper_' . ucfirst($key);
        if (Cola::loadClass($className)) {
            return $this->$key = new $className();
        } else {
            throw new Exception("No helper like $key defined");
        }
    }
}