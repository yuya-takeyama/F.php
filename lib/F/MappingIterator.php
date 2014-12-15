<?php
class F_MappingIterator extends IteratorIterator
{
    private $fn;

    public function __construct(Traversable $iterator, $fn)
    {
        $this->fn = $fn;

        parent::__construct($iterator);
    }

    public function current()
    {
        return call_user_func($this->fn, parent::current());
    }
}
