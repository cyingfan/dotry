<?php
namespace Cyingfan\DoTry;


class ReturnValues
{
    protected $executionValue;
    protected $exceptionValues = [];
    protected $finallyValue;

    /**
     * @return array
     */
    public function getExceptionValues(): array
    {
        return $this->exceptionValues;
    }

    /**
     * @param string $exception
     * @param $value
     * @return $this
     */
    public function addExceptionValues(string $exception, $value)
    {
        if ($value !== null) {
            $e = $this->exceptionValues[$exception] ?? [];
            $e[] = $value;
            $this->exceptionValues[$exception] = $e;
        }
        return $this;
    }
    /**
     * @return mixed
     */
    public function getExecutionValue()
    {
        return $this->executionValue;
    }

    /**
     * @param mixed $executionValue
     * @return ReturnValues
     */
    public function setExecutionValue($executionValue)
    {
        $this->executionValue = $executionValue;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFinallyValue()
    {
        return $this->finallyValue;
    }

    /**
     * @param mixed $finallyValue
     * @return ReturnValues
     */
    public function setFinallyValue($finallyValue)
    {
        $this->finallyValue = $finallyValue;
        return $this;
    }

    /**
     * Get return value from the following and in the order of
     * 1. Execution
     * 2. First exception
     * 3. Finally
     * @return mixed
     */
    public function getValue()
    {
        if ($this->executionValue !== null) {
            return $this->executionValue;
        }
        if (count($this->exceptionValues) > 0) {
            return reset($this->exceptionValues)[0];
        }
        return $this->finallyValue;
    }

}