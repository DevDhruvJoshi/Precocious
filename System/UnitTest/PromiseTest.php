<?php

use PHPUnit\Framework\TestCase;
use System\Config\Promise;
use System\Preload\PromiseExc;

class PromiseTest extends TestCase {
    
    public function testResolve() {
        $promise = new Promise(function($resolve, $reject) {
            $resolve('Success');
        });

        $result = $promise->wait();
        $this->assertEquals('Success', $result);
    }

    public function testReject() {
        $promise = new Promise(function($resolve, $reject) {
            $reject('Failure');
        });

        $this->expectException(PromiseExc::class);
        $this->expectExceptionMessage('Failure');
        $promise->wait();
    }

    public function testThen() {
        $promise = new Promise(function($resolve) {
            $resolve('Data');
        });

        $result = $promise->then(function($value) {
            return $value . ' Processed';
        })->wait();

        $this->assertEquals('Data Processed', $result);
    }

    public function testCatch() {
        $promise = new Promise(function($resolve, $reject) {
            $reject('Error occurred');
        });

        $promise->catch(function($reason) {
            return 'Handled: ' . $reason;
        });

        $this->expectException(PromiseExc::class);
        $this->expectExceptionMessage('Error occurred');
        $promise->wait();
    }

    public function testChaining() {
        $promise = new Promise(function($resolve) {
            $resolve(5);
        });

        $result = $promise
            ->then(function($value) {
                return $value * 2;
            })
            ->then(function($value) {
                return $value + 3;
            })
            ->wait();

        $this->assertEquals(13, $result);
    }

    public function testRejectInExecutor() {
        $promise = new Promise(function($resolve, $reject) {
            throw new Exception('Direct exception');
        });

        $this->expectException(PromiseExc::class);
        $this->expectExceptionMessage('Direct exception');
        $promise->wait();
    }
}
