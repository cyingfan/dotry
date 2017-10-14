<?php

namespace League\DoTry;


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
     * DoTry constructor.
     * @param callable $callable
     */
    public function __construct(callable $callable)
    {
        $this->callable = $callable;
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
     */
    public function finally(callable $finallyHandler)
    {
        $this->finallyHandler = $finallyHandler;
    }

    /**
     * @param array ...$params
     * @return mixed
     * @throws \Throwable
     */
    public function run(...$params)
    {
        try {
            return call_user_func_array($this->callable, $params);
        } catch (\Throwable $t) {
            $handled = false;
            foreach ($this->exceptionHandlers as $catch) {
                list($exceptionClass, $exceptionHandler) = $catch;
                if (is_a($t, $exceptionClass)) {
                    $handled = true;
                    $value = call_user_func($exceptionHandler, $t);
                    if (!is_null($value)) {
                        return $value;
                    }
                }
            }
            if (!$handled) {
                throw $t;
            }
        }
        if ($this->finallyHandler !== null) {
            $value = call_user_func($this->finallyHandler);
            if (!is_null($value)) {
                return $value;
            }
        }
    }
}