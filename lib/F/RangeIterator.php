<?php
/**
 * RangeIterator
 *
 * @author Yuya Takeyama
 */
class F_RangeIterator implements Iterator
{
    private $start;
    private $end;
    private $step;
    private $key;
    private $current;

    public function __construct($start, $end, $step = 1)
    {
        $this->start = $start;
        $this->end   = $end;
        $this->step  = $step;
    }

    public function rewind()
    {
        $this->key     = 0;
        $this->current = $this->start;
    }

    public function next()
    {
        $this->key += 1;
        $this->current += $this->step;
    }

    public function key()
    {
        return $this->key;
    }

    public function current()
    {
        return $this->current;
    }

    public function valid()
    {
        return $this->current() <= $this->end;
    }
}
