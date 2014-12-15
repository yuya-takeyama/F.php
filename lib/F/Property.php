<?php
/**
 * Property reader
 *
 * @author Yuya Takeyama
 */
class F_Property
{
    private $propertyName;

    /**
     * @param string $propertyName
     */
    public function __construct($propertyName)
    {
        $this->propertyName = $propertyName;
    }

    /**
     * @param object $object
     */
    public function __invoke($object)
    {
        return $object->{$this->propertyName};
    }
}
