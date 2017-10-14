<?php

namespace League\DoTry\Tests;

use League\DoTry\DoTry;
use PHPUnit\Framework\TestCase;

class DoTryTest extends TestCase
{
    /**
     * @test
     */
    public function constructor()
    {
        // Lambda
        $this->assertInstanceOf(DoTry::class, new DoTry(function () {
        }));
        // function name
        $this->assertInstanceOf(DoTry::class, new DoTry("var_dump"));
        // Object->method
        $this->assertInstanceOf(DoTry::class, new DoTry([$this, 'assertTrue']));
        // Invokeable object
        $this->assertInstanceOf(
            DoTry::class,
            new DoTry(
                new class
                {
                    public function __invoke()
                    {
                    }
                }
            )
        );
    }

    /**
     * @test
     */
    public function noException()
    {
        $expectedValue = mt_rand(1, PHP_INT_MAX);
        $value = (new DoTry(
            function ($expectedValue) {
                return $expectedValue;
            }
        ))
            ->catch(
                \Throwable::class,
                function () use ($expectedValue) {
                    return $expectedValue - 1;
                })
            ->run($expectedValue);

        $this->assertSame($expectedValue, $value);
    }

    /**
     * @test
     */
    public function withExceptionHandled()
    {
        $expectedValue = mt_rand(1, PHP_INT_MAX);
        $value = (new DoTry(
            function ($expectedValue) {
                throw new \LogicException("Bad logic");
                return $expectedValue;
            }
        ))
            ->catch(
                \LogicException::class,
                function () use ($expectedValue) {
                    return $expectedValue - 1;
                })
            ->run($expectedValue);

        $this->assertSame($expectedValue - 1, $value);
    }

    /**
     * @test
     * @expectedException \Exception
     */
    public function withExceptionUnhandled()
    {
        $expectedValue = mt_rand(1, PHP_INT_MAX);
        $value = (new DoTry(
            function ($expectedValue) {
                throw new \Exception("Bad logic");
            }
        ))
            ->catch(
                \InvalidArgumentException::class,
                function () use ($expectedValue) {
                })
            ->run($expectedValue);
    }

    /**
     * @test
     */
    public function withExceptionHandledMultiple()
    {
        $expectedValue = mt_rand(1, PHP_INT_MAX);
        $logicExceptionHandlerCalled = false;
        $exceptionHandlerCalled = false;
        $value = (new DoTry(
            function ($expectedValue) {
                throw new \LogicException("Bad logic");
            }
        ))
            ->catch(
                \LogicException::class,
                function () use (&$logicExceptionHandlerCalled) {
                    $logicExceptionHandlerCalled = true;
                })
            ->catch(
                \Exception::class,
                function () use (&$exceptionHandlerCalled) {
                    $exceptionHandlerCalled = true;
                })
            ->run($expectedValue);

        $this->assertTrue($logicExceptionHandlerCalled);
        $this->assertTrue($exceptionHandlerCalled);
    }
}
