<?php
/**
 * Operator function which is argument-bound
 *
 * @author Yuya Takeyama
 */
class F_ArgBoundOperator
{
    private $fn;

    private $boundArg;

    public function __construct($fn, $boundArg)
    {
        $this->fn       = $fn;
        $this->boundArg = $boundArg;
    }

    public function __invoke($arg)
    {
        return call_user_func($this->fn, $arg, $this->boundArg);
    }
}
