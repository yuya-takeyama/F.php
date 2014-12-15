<?php
class F_FilterIterator extends IteratorIterator
{
    private $fn;

    public function __construct(Traversable $iterator, $fn)
    {
        $this->fn = $fn;

        parent::__construct($iterator);
    }

    public function rewind()
    {
        parent::rewind();
        $this->skip();
    }

    public function next()
    {
        parent::next();

        while ($this->valid()) {
            if (call_user_func($this->fn, $this->current())) {
                return;
            }

            parent::next();
        }
    }

    private function skip()
    {
        while ($this->valid()) {
            if (call_user_func($this->fn, $this->current(), $this->key())) {
                return;
            }

            parent::next();
        }
    }
}
