<?php

namespace Cyingfan\DoTry;

class DoTry
{
    /**
     * @var callable
     */
    protected $callable;
    /**
     * @var array
     */
    protected $exceptionHandlers = [];
    /**
     * @var callable
     */
    protected $finallyHandler;
    /**
     * @var ReturnValues
     */
    protected $returnValues;

    /**
     * DoTry constructor.
     * @param callable $callable
     */
    public function __construct(callable $callable)
    {
        $this->callable = $callable;
        $this->returnValues = new ReturnValues();
    }

    /**
     * @param string $exceptionClass
     * @param callable $exceptionHandler
     * @return $this
     */
    public function catch(string $exceptionClass, callable $exceptionHandler)
    {
        if ($exceptionClass !== \Throwable::class && !is_subclass_of($exceptionClass, \Throwable::class)) {
            throw new \InvalidArgumentException("Class name must be subclass of " . \Throwable::class);
        }
        $this->exceptionHandlers[] = [$exceptionClass, $exceptionHandler];
        return $this;
    }

    /**
     * @param callable $finallyHandler
     * @return $this
     */
    public function finally(callable $finallyHandler)
    {
        $this->finallyHandler = $finallyHandler;
        return $this;
    }

    /**
     * @param array ...$params
     * @return ReturnValues
     * @throws \Throwable
     */
    public function run(...$params)
    {
        $finalReturnValue = null;
        $unhandledException = null;
        try {
            $this->returnValues->setExecutionValue(
                call_user_func_array($this->callable, $params)
            );
        } catch (\Throwable $t) {
            $handled = false;
            foreach ($this->exceptionHandlers as $catch) {
                list($exceptionClass, $exceptionHandler) = $catch;
                if (is_a($t, $exceptionClass)) {
                    $handled = true;
                    $this->returnValues->addExceptionValues(
                        get_class($t),
                        call_user_func($exceptionHandler, $t)
                    );
                }
            }
            if (!$handled) {
                $unhandledException = $t;
            }
        }
        if ($this->finallyHandler !== null) {
            $this->returnValues->setFinallyValue(
                call_user_func($this->finallyHandler)
            );
        }
        if ($unhandledException !== null) {
            throw $unhandledException;
        }

        return $this->returnValues;
    }

    public function retry(int $times)
    {
        if ($times < 1) {
            throw new \InvalidArgumentException("Retry must be at least 1");
        }
        // TODO
    }
}