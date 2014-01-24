<?php

namespace G4\Mcache\Driver;

use G4\Mcache\Driver\DriverAbstract;

class Libmemcached extends DriverAbstract
{

    /**
     * @var bool
     */
    private $_compression;

    /**
     * @var array
     */
    private $_servers = array();

    /**
     * @param string $host
     * @param int $port
     * @param int $weight
     *
     * @return \G4\Mcache\Driver\Libmemcached
     */
    private function _processOptions()
    {
        $options = $this->getOptions();

        if(empty($options)) {
            throw new \Exception('Options must be set');
        }

        foreach($options['servers'] as $server) {

            if(empty($server['host']) || !is_string($server['host'])) {
                throw new \Exception('Server host is invalid');
            }

            $port = empty($server['port']) ? $server['port'] : 11211;

            $this->_servers[] = array(
                'host'   => $server['host'],
                'port'   => $port,
            );

            if(!empty($server['weight'])) {
                $this->_servers['weight'] = $server['weight'];
            }
        }

        if(isset($options['compression'])) {
            $this->_compression = $options['compression'];
        }

        return $this;
    }

    public function get($key)
    {
        return $this->_connect()->get($key);
    }

    public function set($key, $value, $expiration)
    {
        return $this->_connect()->set($key, $value, $expiration);
    }

    public function delete($key)
    {
        return $this->_connect()->delete($key);
    }

    public function replace($key, $value, $expiration)
    {
        return $this->_connect()->replace($key, $value, $expiration);
    }

    /**
     * @return \Memcached
     */
    protected function _connect()
    {
        if(! $this->_driver instanceof \Memcached) {
            $this->_driverFactory();
        }

        return $this->_driver;
    }

    private function _driverFactory()
    {
        $this->_processOptions();

        $this->_driver = new \Memcached();
        $this->_driver->addServers($this->_servers);

        if (isset($this->_compression)) {
            $this->_driver->setOption(\Memcached::OPT_COMPRESSION, $this->_compression);
        }
    }
}