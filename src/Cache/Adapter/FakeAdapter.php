<?php

namespace Cola\Cache\Adapter;

class FakeAdapter extends AbstractAdapter
{
    /**
     * Set cache
     *
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     * @return boolean
     */
    public function set($key, $value, $ttl = null)
    {
        return true;
    }

    public function setMultiple($values, $ttl = null)
    {
        return true;
    }

    /**
     * Get Cache
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return null;
    }

    public function getMultiple($keys, $default = null)
    {
        $rps = [];
        foreach ($keys as $key) {
            $rps[$key] = $default;
        }

        return $rps;
    }

    /**
     * Delete cache
     * @param string $id
     * @return boolean
     */
    public function delete($key)
    {
        return true;
    }

    public function deleteMultiple($keys)
    {
        return true;
    }

    /**
     * clear cache
     */
    public function clear()
    {
        return true;
    }

    public function has($key)
    {
        return false;
    }
}