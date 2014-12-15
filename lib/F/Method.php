<?php
/**
 * Method caller
 *
 * @author Yuya Takeyama
 */
class F_Method
{
    private $name;

    private $args;

    /**
     * @param string $name
     * @param array  $args
     */
    public function __construct($name, $args)
    {
        $this->name = $name;
        $this->args = $args;
    }

    /**
     * @param object $object
     */
    public function __invoke($object)
    {
        return call_user_func_array(array($object, $this->name), $this->args);
    }
}
