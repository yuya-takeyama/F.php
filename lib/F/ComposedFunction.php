<?php
/**
 * Composed function
 *
 * @author Yuya Takeyama
 */
class F_ComposedFunction
{
    private $fns;

    public function __construct(array $fns)
    {
        $this->fns = array_reverse($fns);
    }

    public function __invoke($arg)
    {
        return F::foldl(array($this, 'apply'), $arg, $this->fns);
    }

    public function apply($acc, $fn)
    {
        return call_user_func($fn, $acc);
    }
}
