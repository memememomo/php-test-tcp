<?php

require_once 'TestTCPServer.php';

use uchiko\Test\TCP;

class AbrtTest extends PHPUnit_Framework_TestCase
{
    public function testAbrt()
    {
        $self = $this;
        uchiko\Test\TCP::test_tcp(array(
            'client' => function($port, $pid) use ($self) {
                $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
                if (! socket_connect($sock, '127.0.0.1', $port)) {
                    throw new \Exception("Cannot open client socket:".socket_strerror(socket_last_error()));
                }

                socket_write($sock, "dump\n");
                $res = socket_read($sock, 5);
                $self->assertEquals("dump\n", $res);
                socket_close($sock);
                $self->assertTrue(true);
            },
            'server' => function($port) use ($self) {
                $server = new TestTCPServer('127.0.0.1', $port);
                $server->run(function($remote, $line) {
                    socket_write($remote, $line);
                    if (preg_match('/dump/', $line)) {
                        // TODO: CORE::dump()
                        return "dump\n";
                    }
                });
            }
        ));
    }
}
