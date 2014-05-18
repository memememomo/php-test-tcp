<?php

require_once 'TestTCPServer.php';

use uchiko\Test\TCP;

class SimpleTest extends PHPUnit_Framework_TestCase
{
    public function testSimple()
    {
        $self = $this;
        uchiko\Test\TCP::test_tcp(array(
            'client' => function($port, $pid) use ($self) {
                for ($i = 0; $i < 10; $i++) {
                    $self->assertRegExp('/^[0-9]+$/', "$port");
                }

                $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
                if (! socket_connect($sock, '127.0.0.1', $port) ) {
                    throw new \Exception("Cannot open client socket:".socket_strerror(socket_last_error()));
                }

                socket_write($sock, "foo\n", 4);
                $res = socket_read($sock, 4);
                $self->assertEquals("foo\n", $res);

                socket_write($sock, "bar\n");
                $res = socket_read($sock, 4);
                $self->assertEquals("bar\n", $res);

                socket_write($sock, "quit\n");

                socket_close($sock);
            },
            'server' => function($port) use ($self) {
                for ($i = 0; $i < 10; $i++) {
                    $self->assertRegExp('/^[0-9]+$/', "$port");
                }
                $server = new TestTCPServer('127.0.0.1', $port);
                $server->port = $port;
                $server->run(function($remote, $line, $sock) {
                    socket_write($remote, $line);
                });
            },
        ));
    }
}
