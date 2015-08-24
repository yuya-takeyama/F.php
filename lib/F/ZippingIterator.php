<?php
class F_ZippingIterator implements Iterator
{
    private $iterators;

    private $i;

    public function __construct(array $iterators)
    {
        $this->iterators = $iterators;
    }

    public function rewind()
    {
        foreach ($this->iterators as $iterator) {
            $iterator->rewind();
        }

        $this->i = 0;
    }

    public function key()
    {
        return $this->i;
    }

    public function current()
    {
        return F::toArray(F::map(F::method('current'), $this->iterators));
    }

    public function next()
    {
        foreach ($this->iterators as $iterator) {
            $iterator->next();
        }

        $this->i++;
    }

    public function valid()
    {
        return F::all(F::method('valid'), $this->iterators);
    }
}
