<?php

require_once 'TestTCPServer.php';

use uchiko\Test\TCP;

class DieTest extends PHPUnit_Framework_TestCase
{
    public function testDie()
    {
        $self = $this;
        $child_pid = null;
        try {
            uchiko\Test\TCP::test_tcp(array(
                'client' => function($port, $pid) use ($self, &$child_pid) {
                    $child_pid = $pid;
                    sleep(3); # wait till the server actually starts
                    throw new \Exception("sinamon");
                },
                'server' => function($port) use ($self) {
                    $server = new TestTCPServer('127.0.0.1', $port);
                    $server->run(function() {});
                }
            ));
        } catch (Exception $e) {
            $this->assertRegExp('/sinamon/', $e->getMessage());
            $killed = posix_kill($child_pid, SIGKILL);
            $this->assertEquals(0, $killed);
        }
    }
}
