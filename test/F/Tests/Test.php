<?php
/**
 * Unit-test class For F
 *
 * @author Yuya Takeyama
 */
class F_Tests_MethodCallInspector
{
    public function __call($method, $args)
    {
        return array(
            'name' => $method,
            'args' => $args,
        );
    }
}

class F_Tests_Test extends PHPUnit_Framework_TestCase
{
    public function test_range()
    {
        $this->assertIterator(array(), F::range(1, 0));
        $this->assertIterator(array(1, 2, 3, 4, 5), F::range(1, 5));
        $this->assertIterator(array(1, 3, 5, 7, 9), F::range(1, 10, 2));
    }

    public function test_map()
    {
        $this->assertIterator(
            array(true, false, true, false, true),
            F::map(array($this, 'odd'), F::range(1, 5))
        );
        $this->assertIterator(
            array(false, true, false, true, false),
            F::map(F::not(array($this, 'odd')), F::range(1, 5))
        );
        $this->assertIterator(array(2, 3, 4, 5, 6), F::map(F::op('+', 1), F::range(1, 5)));
    }

    public function test_filter()
    {
        $this->assertIterator(
            array(1, 3, 5),
            F::filter(array($this, 'odd'), F::range(1, 5))
        );
        $this->assertIterator(
            array(2, 4),
            F::filter(F::not(array($this, 'odd')), F::range(1, 5))
        );
    }

    public function test_foldl()
    {
        $this->assertSame(55, F::foldl(F::op('+'), 0, F::range(1, 10)));
        $this->assertSame(0, F::foldl(F::op('+'), 0, array()));
    }

    public function test_foldl1()
    {
        $strictAdd = array($this, 'strictAdd');

        $this->assertSame(55, F::foldl1($strictAdd, F::range(1, 10)));
        $this->assertSame(1, F::foldl1($strictAdd, array(1)));
        $this->assertNull(F::foldl1($strictAdd, array()));
    }

    public function test_any()
    {
        $this->assertTrue(F::any(array($this, 'odd'), array(2, 4, 5)));
        $this->assertFalse(F::any(array($this, 'odd'), array(2, 4, 6)));
        $this->assertFalse(F::any(array($this, 'odd'), array()));
    }

    public function test_all()
    {
        $this->assertTrue(F::all(array($this, 'odd'), array(1, 3, 5)));
        $this->assertFalse(F::all(array($this, 'odd'), array(1, 3, 4)));
        $this->assertTrue(F::all(array($this, 'odd'), array()));
    }

    public function test_compose()
    {
        $this->assertIterator(
            array(4, 6, 8, 10, 12),
            F::map(F::compose(F::op('*', 2), F::op('+', 1)), F::range(1, 5))
        );
        $this->assertIterator(
            array(3, 5, 7, 9, 11),
            F::map(F::compose(F::op('+', 1), F::op('*', 2)), F::range(1, 5))
        );
        $this->assertIterator(
            array(-3, -1, 1, 3, 5),
            F::map(
                F::compose(F::op('+', 1), F::op('*', 2), F::op('-', 3)),
                F::range(1, 5)
            )
        );
    }

    public function test_operatorMinus()
    {
        $this->assertEquals(1, call_user_func(F::op('-'), 2, 1));
        $this->assertEquals(1, call_user_func(F::op('-', 1), 2));
    }

    public function test_operatorDot()
    {
        $this->assertSame('foobar', call_user_func(F::op('.'), 'foo', 'bar'));
        $this->assertSame('foobar', call_user_func(F::op('.', 'bar'), 'foo'));
    }

    public function test_operatorPow()
    {
        $this->assertEquals(1, call_user_func(F::op('**'), 2, 0));
        $this->assertEquals(256, call_user_func(F::op('**'), 2, 8));
        $this->assertEquals(-1, call_user_func(F::op('**'), -1, 1));
        $this->assertEquals(1, call_user_func(F::op('**'), -1, 2));
        $this->assertEquals(1, call_user_func(F::op('**'), 0, 0));
        $this->assertEquals(0, call_user_func(F::op('**'), 0, 1));

        $this->assertEquals(1, call_user_func(F::op('**', 0), 2));
        $this->assertEquals(256, call_user_func(F::op('**', 8), 2));
        $this->assertEquals(-1, call_user_func(F::op('**', 1), -1));
        $this->assertEquals(1, call_user_func(F::op('**', 2), -1));
        $this->assertEquals(1, call_user_func(F::op('**', 0), 0));
        $this->assertEquals(0, call_user_func(F::op('**', 1), 0));
    }

    public function test_not()
    {
        $isNotString = F::not('is_string');

        $this->assertTrue(call_user_func_array($isNotString, 0));
        $this->assertFalse(call_user_func_array($isNotString, '0'));
    }

    public function test_index()
    {
        $getSecondElement = F::index(2);

        $this->assertSame(
            'Baz',
            call_user_func($getSecondElement, array('Foo', 'Bar', 'Baz'))
        );
    }

    public function test_property()
    {
        $struct = new stdClass;
        $struct->foo = 'FOO VALUE';

        $getFooValue = F::property('foo');

        $this->assertSame('FOO VALUE', call_user_func($getFooValue, $struct));
    }

    public function test_method()
    {
        $obj = new F_Tests_MethodCallInspector;

        $fooMethodWithEmptyArguments = F::method('foo');

        $this->assertSame(
            array(
                'name' => 'foo',
                'args' => array(),
            ),
            call_user_func(
                $fooMethodWithEmptyArguments,
                $obj
            )
        );

        $barMethodWithBazAndQuxArguments = F::method('bar', array('baz', 'qux'));

        $this->assertSame(
            array(
                'name' => 'bar',
                'args' => array('baz', 'qux'),
            ),
            call_user_func(
                $barMethodWithBazAndQuxArguments,
                $obj
            )
        );

    }

    public function test_lambda()
    {
        $pow3 = F::lambda('$x', 'return pow($x, 3);');

        $this->assertSame(8, call_user_func($pow3, 2));
    }

    public function test_lambda_returns_same_string()
    {
        $fnArg  = '$x';
        $fnBody = 'return $x;';

        $this->assertSame(F::lambda($fnArg, $fnBody), F::lambda($fnArg, $fnBody));
    }

    public function test_join()
    {
        $this->assertSame('', F::join(',', array()));
        $this->assertSame('a,b,c', F::join(',', array('a', 'b', 'c')));
        $this->assertSame('', F::join(',', new ArrayIterator(array())));
        $this->assertSame('a,b,c', F::join(',', new ArrayIterator(array('a', 'b', 'c'))));
    }

    public function test_concat()
    {
        $this->assertIterator(
            array(1, 2, 3, 4, 5),
            F::concat(
                array(
                    F::range(1, 3),
                    array(4, 5)
                )
            )
        );
    }

    public function test_take()
    {
        $this->assertIterator(array(1, 2, 3), F::take(3, F::range(1, INF)));
        $this->assertIterator(array(1, 2, 3), F::take(3, array(1, 2, 3, 4, 5)));

        $this->assertIterator(array(), F::take(0, F::range(1, INF)));
        $this->assertIterator(array(), F::take(0, array(1, 2, 3, 4, 5)));

        $this->assertIterator(array(1, 2, 3), F::take(5, array(1, 2, 3)));
        $this->assertIterator(array(1, 2, 3), F::take(5, new ArrayIterator(array(1, 2, 3))));
    }

    public function test_drop()
    {
        $this->assertIterator(array(3, 4, 5), F::drop(2, F::range(1, 5)));
        $this->assertIterator(array(3, 4, 5), F::drop(2, array(1, 2, 3, 4, 5)));

        $this->assertIterator(array(), F::drop(5, F::range(1, 5)));
        $this->assertIterator(array(), F::drop(5, array(1, 2, 3, 4, 5)));

        $this->assertIterator(array(), F::drop(6, F::range(1, 5)));
        $this->assertIterator(array(), F::drop(6, array(1, 2, 3, 4, 5)));
    }

    public function test_count()
    {
        $this->assertEquals(3, F::count(new ArrayIterator(array(1, 2, 3))));
        $this->assertEquals(3, F::count(array(1, 2, 3)));
        $this->assertEquals(3, F::count(F::range(1, 3)));
    }

    public function test_zip()
    {
        $this->assertIterator(
            array(
                array(1, 4),
                array(2, 5),
                array(3, 6),
            ),
            F::zip(
                array(1, 2, 3),
                array(4, 5, 6)
            )
        );
        $this->assertIterator(
            array(
                array(1, 4),
                array(2, 5),
                array(3, 6),
            ),
            F::zip(
                array(1, 2, 3, 4),
                array(4, 5, 6)
            )
        );
        $this->assertIterator(
            array(
                array(1, 4),
                array(2, 5),
                array(3, 6),
            ),
            F::zip(
                array(1, 2, 3),
                array(4, 5, 6, 7)
            )
        );
        $this->assertIterator(
            array(
                array(1, 4, 7),
                array(2, 5, 8),
                array(3, 6, 9),
            ),
            F::zip(
                array(1, 2, 3),
                array(4, 5, 6),
                array(7, 8, 9)
            )
        );
    }

    public function test_product()
    {
        $this->assertIterator(array(), F::product());
        $this->assertIterator(array(), F::product(array(1), array()));
        $this->assertIterator(array(), F::product(array(1), array(2), array()));
        $this->assertIterator(array(array(1), array(2)), F::product(array(1, 2)));
        $this->assertIterator(
            array(
                array(1, 3),
                array(1, 4),
                array(2, 3),
                array(2, 4),
            ),
            F::product(
                array(1, 2),
                array(3, 4)
            )
        );
        $this->assertIterator(
            array(
                array(1, 4, 7),
                array(1, 4, 8),
                array(1, 4, 9),
                array(1, 5, 7),
                array(1, 5, 8),
                array(1, 5, 9),
                array(1, 6, 7),
                array(1, 6, 8),
                array(1, 6, 9),
                array(2, 4, 7),
                array(2, 4, 8),
                array(2, 4, 9),
                array(2, 5, 7),
                array(2, 5, 8),
                array(2, 5, 9),
                array(2, 6, 7),
                array(2, 6, 8),
                array(2, 6, 9),
                array(3, 4, 7),
                array(3, 4, 8),
                array(3, 4, 9),
                array(3, 5, 7),
                array(3, 5, 8),
                array(3, 5, 9),
                array(3, 6, 7),
                array(3, 6, 8),
                array(3, 6, 9),
            ),
            F::product(
                array(1, 2, 3),
                array(4, 5, 6),
                array(7, 8, 9)
            )
        );
    }

    public function test_cycle()
    {
        $expected = array(
            1, 2, 3, 4, 5, 6, 7, 8, 9, 10,
            1, 2, 3, 4, 5, 6, 7, 8, 9, 10,
            1, 2, 3, 4, 5, 6, 7, 8, 9, 10,
            1, 2, 3, 4, 5, 6, 7, 8, 9, 10,
            1, 2, 3, 4, 5, 6, 7, 8, 9, 10,
            1, 2, 3, 4, 5, 6, 7, 8, 9, 10,
            1, 2, 3, 4, 5, 6, 7, 8, 9, 10,
            1, 2, 3, 4, 5, 6, 7, 8, 9, 10,
            1, 2, 3, 4, 5, 6, 7, 8, 9, 10,
            1, 2, 3, 4, 5, 6, 7, 8, 9, 10,
        );

        // array
        $this->assertIterator(
            $expected,
            F::take(100, F::cycle(range(1, 10)))
        );
        // Iterator
        $this->assertIterator(
            $expected,
            F::take(100, F::cycle(F::range(1, 10)))
        );
        // IteratorAggregate
        $this->assertIterator(
            $expected,
            F::take(100, F::cycle(new ArrayObject(range(1, 10))))
        );
    }

    public function test_toArray()
    {
        $this->assertSame(
            array(3, 4, 5),
            F::toArray(F::take(3, F::drop(2, F::range(1, INF))))
        );
    }

    public function test_toArrayWithKeys()
    {
        $this->assertSame(
            array(
                2 => 3,
                3 => 4,
                4 => 5,
            ),
            F::toArrayWithKeys(F::take(3, F::drop(2, F::range(1, INF))))
        );
    }

    public function assertIterator($expectedArray, Traversable $actualIterator)
    {
        $this->assertSame(
            $expectedArray,
            F::toArray($actualIterator)
        );
    }

    public function odd($n)
    {
        return $n % 2 == 1;
    }

    public function strictAdd($a, $b)
    {
        if (is_int($a) === false) {
            throw new InvalidArgumentException('argument #1 must be integer');
        }

        if (is_int($b) === false) {
            throw new InvalidArgumentException('argument #2 must be integer');
        }

        return $a + $b;
    }
}
