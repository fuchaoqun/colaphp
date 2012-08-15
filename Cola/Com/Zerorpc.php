<?php
class Cola_Com_Zerorpc
{
    protected $_zmq;

    public $timeout = 1000;

    public $sleep   = 5;

    protected $_times;

    public $error = array();

    public static function __construct($server, $timeout = null, $retries = null)
    {
        $this->_zmq = new ZMQ(new ZMQContext(), ZMQ::SOCKET_REQ);

        if (!is_null($timeout)) {
            $this->timeout = $timeout;
        }
        if (!is_null($retries)) {
            $this->retries = $retries;
        }
        $this->_times = ceil($this->timeout/$this->sleep);

        $this->_zmq->setSockOpt(ZMQ::SOCKOPT_LINGER, $this->timeout);


        $this->_zmq->connect($server); //tcp://127.0.0.1:4242
    }

    /**
     * Call RPC Function
     *
     * @param string $func
     * @param array $args
     * @return mixed
     */
    public function call($func, $args)
    {
        $data = $this->_formatRequestMessage($func, $args);
        $msg  = $this->pack($data);
        if (!$this->_send($msg)) {
            return false;
        }

        if ($data = $this->_receive()) {
            return $data[2][0];
        }

        return false;
    }

    /**
     * Send ZeroRPC Request
     *
     * @param string $msg
     * @return boolean
     */
    protected function _send($msg)
    {
        try {
            if ($res = $this->_zmq->send($msg, ZMQ::MODE_DONTWAIT)) {
                return true;
            }
            throw new Exception('rpc send failed.', -1);
        } catch (Exception $e) {
            $this->error = array('code' => $e->getCode(), 'msg' => $e->getMessage());
            return false;
        }
    }

    /**
     * Receive ZeroRPC Response
     *
     * @return mixed
     */
    protected function _receive()
    {
        for ($i = 0; $i < $this->_times; $i ++) {
            try {
                if ($rps = $this->_zmq->recv(ZMQ::MODE_DONTWAIT)) {
                    return self::unpack($rps);
                }
                continue;
            } catch (Exception $e) {
                $this->error = array('code' => $e->getCode(), 'msg' => $e->getMessage());
                return false;
            }
            usleep($this->sleep);
        }

        $this->error = array('code' => -2, 'msg' => 'rpc receive timeout.');
        return false;
    }

    /**
     * Format ZeroRPC Request Data
     *
     * @param string $func
     * @param array $args
     * @return string
     */
    protected function _formatRequestMessage($func, $args)
    {
        $data = array(
            array('message_id' => uniqid(''), 'v' => 3),
            $func,
            $args
        );

        return self::pack($data);
    }

    public static function pack($data)
    {
        return msgpack_pack($data);
    }

    public static function unpack($str)
    {
        $data = msgpack_unpack($str);
    }
}
