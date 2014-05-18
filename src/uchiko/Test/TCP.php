<?php

namespace uchiko\Test;

use dooaki\Net\EmptyPort;

class TCP
{
    private $auto_start;
    private $max_wait;
    private $_my_pid;
    private $pid;
    private $port;

    static public function test_tcp($args = [])
    {
        foreach (["client", "server"] as $k) {
            if ( ! array_key_exists($k, $args) ) {
                throw new \Exception("missing madatory parameter $k");
            }
        }


        $server_code = $args['server'];
        unset($args['server']);

        $port = array_key_exists('port', $args) ? $args['port'] : EmptyPort::find();
        unset($args['port']);

        $client_code = array_key_exists('client', $args) ? $args['client'] : null;
        unset($args['client']);

        $server = new TCP(array_merge([
            'code' => $server_code,
            'port' => $port,
        ], $args));
        $client_code($server->port, $server->pid);
        $server = NULL; // make sure
    }

    public function wait_port($port, $max_wait, $retry = 0)
    {
        if (!$retry) {
            $max_wait = $max_wait * $retry;
        }

        if (!$max_wait) { $max_wait = 10; }

        if ( ! EmptyPort::wait($port, $max_wait) ) {
            throw new \Exception("cannot open port: $port");
        }
    }

    public function __construct($args = [])
    {
        if ( ! array_key_exists('code', $args) ) {
            throw new \Exception("missing mandatory parameter 'code'");
        }
        $this->code = $args['code'];

        if ( ! array_key_exists('port', $args) ) {
            $this->port = EmptyPort::find();
        } else {
            $this->port = $args['port'];
        }

        $this->auto_start = array_key_exists('auto_start', $args) ? $args['auto_start'] : 1;
        $this->max_wait   = array_key_exists('max_wait', $args) ? $args['max_wait'] : 10;
        $this->_my_pid    = array_key_exists('_my_pid', $args) ?  $args['_my_pid'] : getmypid();

        if ($this->auto_start) {
            $this->start();
        }

        return $this;
    }

    public function pid()
    {
        return $this->pid;
    }

    public function port()
    {
        return $this->port;
    }

    public function start()
    {
        $pid = pcntl_fork();

        if ( $pid == -1 ) {
            throw new \Exception("pcntl_fork() failed");
        }

        if ( $pid ) { // parent process.
            self::wait_port($this->port, $this->max_wait);
            return;
        } else { // child process
            $code = $this->code;
            $code($this->port);
            // should not reach here
            if ( posix_kill($this->_my_pid, SIGTERM) ) { // warn only parent process still exists
                echo "[test-tcp] Child process does not block(PID: ".getmypid().", PPID: ".$this->_my_pid.")\n";
            }
            exit(0);
        }
    }

    public function stop()
    {
        if ( ! $this->pid ) {
            return;
        }

        if ( $this->_my_pid != getmypid() ) {
            return;
        }

        posix_kill($this->pid, SIGTERM);

        while (1) {
            $kid = pcntl_waitpid( $this->pid, $status );
            if (pcntl_wifsignaled($status)) {
                $signo = pcntl_wtermsig($status);
                if ($signo == SIGABRT || $signo == SIGPIPE) {
                    echo "your server received SIG$signame";
                }
            }
            if ($kid == 0 || $kid == -1) {
                break;
            }
        }

        $this->pid = NULL;
    }

    public function __destruct()
    {
        $this->stop();
    }
}
