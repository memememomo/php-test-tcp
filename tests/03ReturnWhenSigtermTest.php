<?php

require_once 'TestTCPServer.php';

use uchiko\Test\TCP;

class ReturnWhenSigtermTest extends PHPUnit_Framework_TestCase
{
    public function testReturnWhenSigterm()
    {
        $self = $this;

        uchiko\Test\TCP::test_tcp(array(
            'client' => function($port, $pid) use ($self) {
                $self->assertTrue(true);
                // nop... but after this statement, Test\TCP send SIGTERM to server process.
            },
            'server' => function($port) use ($self) {
                $server = new TestTCPServer('127.0.0.1', $port);
                $sock = $server->sock;
                $term_received = 0;
                pcntl_signal(SIGTERM, function ($signo) {
                    $term_received++;
                });
                while ($term_received == 0) {
                    $csock = socket_accept($sock);
                    if ($csock) {
                        socket_close($csock);
                    }
                }

                // suppress warnings: [Test\TCP] Child process does not block(PID: 84792, PPID: 84791)
                pctnl_signal(SIGWARN, function($signo) {});
            }
        ));

        $this->assertTrue(true); // test finished
    }
}
