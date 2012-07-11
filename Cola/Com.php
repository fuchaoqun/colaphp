<?php
/**
 *
 */

class Cola_Com
{
    /**
     * Dynamic get Component
     *
     * @param string $key
     */
    public function __get($key)
    {
        $className = 'Cola_Com_' . ucfirst($key);
        if (Cola::loadClass($className)) {
            return $this->$key = new $className();
        } else {
            throw new Cola_Exception("No component like $key defined");
        }
    }
}