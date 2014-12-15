<?php
/**
 * Logical-not operator
 *
 * @author Yuya Takeyama
 */
class F_Not
{
    private $fn;

    /**
     * @param callable $fn
     */
    public function __construct($fn)
    {
        F::assertCallable($fn);

        $this->fn = $fn;
    }

    public function __invoke()
    {
        $args = func_get_args();

        return !call_user_func_array($this->fn, $args);
    }
}
