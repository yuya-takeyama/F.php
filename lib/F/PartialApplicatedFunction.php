<?php
/**
 * Partial applicated function
 *
 * @author Yuya Takeyama
 */
class F_PartialApplicatedFunction
{
    private $fn;

    private $boundArgs;

    public function __construct($fn, $boundArgs)
    {
        $this->fn        = $fn;
        $this->boundArgs = $boundArgs;
    }

    public function __invoke()
    {
        $args = func_get_args();
        $args = array_merge($this->boundArgs, $args);

        return call_user_func_array($this->fn, $args);
    }
}
