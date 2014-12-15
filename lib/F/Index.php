<?php
/**
 * Index reader
 *
 * @author Yuya Takeyama
 */
class F_Index
{
    private $index;

    /**
     * @param int|string $index
     */
    public function __construct($index)
    {
        $this->index = $index;
    }

    /**
     * @param  array|ArrayAccess $arr
     * @return mixed
     */
    public function __invoke($arr)
    {
        return $arr[$this->index];
    }
}
