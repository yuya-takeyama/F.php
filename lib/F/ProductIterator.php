<?php
class F_ProductIterator implements Iterator
{
    private $iterators;

    private $i;

    private $iteratorCount;

    private $finished;

    public function __construct(array $iterators)
    {
        $this->iterators = $iterators;
        $this->iteratorCount = count($this->iterators);
    }

    public function rewind()
    {
        foreach ($this->iterators as $iterator) {
            $iterator->rewind();
        }

        $this->i = 0;

        if ($this->iteratorCount === 0) {
            $this->finished = true;
        } elseif (F::any(F::not(F::method('valid')), $this->iterators)) {
            $this->finished = true;
        } else {
            $this->finished = false;
        }
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
        for ($i = ($this->iteratorCount - 1); $i >= 0; $i--) {
            $this->iterators[$i]->next();

            if (!$this->iterators[$i]->valid()) {
                $this->iterators[$i]->rewind();
            } else {
                return;
            }
        }

        $this->finished = true;
    }

    public function valid()
    {
        return !$this->finished;
    }
}
