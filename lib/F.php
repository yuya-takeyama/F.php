<?php
/**
 * F.php
 *
 * Functional primitives for Legacy PHP
 *
 * @author Yuya Takeyama
 */
class F
{
    private static $operators;

    private static $lambdas = array();

    public static function range($start, $end, $step = 1)
    {
        return new F_RangeIterator($start, $end, $step);
    }

    public static function map($fn, $iterable)
    {
        return new F_MappingIterator(self::toIterator($iterable), $fn);
    }

    public static function filter($fn, $iterable)
    {
        return new F_FilterIterator(self::toIterator($iterable), $fn);
    }

    public static function foldl($fn, $acc, $iterable)
    {
        foreach ($iterable as $value) {
            $acc = call_user_func($fn, $acc, $value);
        }

        return $acc;
    }

    public static function foldl1($fn, $iterable)
    {
        $first = true;
        $acc = NULL;

        foreach ($iterable as $value) {
            if ($first) {
                $acc = $value;
                $first = false;
            } else {
                $acc = call_user_func($fn, $acc, $value);
            }
        }

        return $acc;
    }

    public static function not($fn)
    {
        return array(new F_Not($fn), '__invoke');
    }

    public static function any($fn, $iterable)
    {
        foreach ($iterable as $value) {
            if (call_user_func($fn, $value)) {
                return true;
            }
        }

        return false;
    }

    public static function all($fn, $iterable)
    {
        foreach ($iterable as $value) {
            if (!call_user_func($fn, $value)) {
                return false;
            }
        }

        return true;
    }

    public static function partialApply($fn, $args)
    {
        return array(new F_PartialApplicatedFunction($fn, $args), '__invoke');
    }

    public static function compose(/* functions to compose */)
    {
        $fns = func_get_args();

        return array(new F_ComposedFunction($fns), '__invoke');
    }

    public static function pipeline(/* functions to compose */)
    {
        $fns = func_get_args();

        return array(new F_ComposedFunction(array_reverse($fns)), '__invoke');
    }

    public static function concat($iterable)
    {
        $result = new AppendIterator;

        foreach ($iterable as $innerIterable) {
            $result->append(self::toIterator($innerIterable));
        }

        return $result;
    }

    public static function join($delim, $iterable)
    {
        $first = true;
        $result = '';

        foreach ($iterable as $value) {
            if ($first) {
                $result = $value;
                $first = false;
            } else {
                $result .= $delim . $value;
            }
        }

        return $result;
    }

    public static function take($n, $iterable)
    {
        if ($n === 0) {
            return new EmptyIterator;
        } else {
            return new LimitIterator(self::toIterator($iterable), 0, $n);
        }
    }

    public static function drop($n, $iterable)
    {
        // LimitIterator は与えられた Iterator が SeekableIterator だと
        // 要素数と同数以上の $n を与えられると OutOfBoundsException を投げるらしいので
        // Seekable でなくする
        return new LimitIterator(self::toNoSeekableIterator($iterable), $n);
    }

    public static function toIterator($iterable)
    {
        if ($iterable instanceof IteratorAggregate) {
            return $iterable->getIterator();
        } elseif ($iterable instanceof Traversable) {
            return $iterable;
        } elseif (is_array($iterable)) {
            return new ArrayIterator($iterable);
        } else {
            throw new InvalidArgumentException('specified value is not iterable');
        }
    }

    public static function toNoSeekableIterator($iterable)
    {
        $iterator = self::toIterator($iterable);

        if ($iterator instanceof SeekableIterator) {
            return new IteratorIterator($iterator);
        } else {
            return $iterator;
        }
    }

    public static function count($iterable)
    {
        if ($iterable instanceof Countable) {
            return count($iterable);
        } else {
            $i = 0;

            foreach ($iterable as $value) {
                $i++;
            }

            return $i;
        }
    }

    public static function toArray($iterable)
    {
        $array = array();

        foreach ($iterable as $value) {
            $array[] = $value;
        }

        return $array;
    }

    public static function toArrayWithKeys($iterable)
    {
        $array = array();

        foreach ($iterable as $key => $value) {
            $array[$key] = $value;
        }

        return $array;
    }

    public static function op($operator)
    {
        $args     = func_get_args();
        $operator = array_shift($args);

        if (count($args) === 0) {
            return self::getOperator($operator);
        } elseif (count($args === 1)) {
            return array(
                new F_ArgBoundOperator(self::getOperator($operator), $args[0]),
                '__invoke',
            );
        } else {
            throw new InvalidArgumentException('too many arguments');
        }
    }

    public static function getOperator($operator)
    {
        if (is_null(self::$operators)) {
            self::$operators = array(
                '+'   => create_function('$x, $y', 'return $x + $y;'),
                '-'   => create_function('$x, $y', 'return $x - $y;'),
                '*'   => create_function('$x, $y', 'return $x * $y;'),
                '/'   => create_function('$x, $y', 'return $x / $y;'),
                '%'   => create_function('$x, $y', 'return $x % $y;'),
                '**'  => create_function('$x, $y', 'return pow($x, $y);'),
                '.'   => create_function('$x, $y', 'return $x . $y;'),
                '===' => create_function('$x, $y', 'return $x === $y;'),
                '!==' => create_function('$x, $y', 'return $x !== $y;'),
            );
        }

        return self::$operators[$operator];
    }

    public static function index($index)
    {
        return array(new F_Index($index), '__invoke');
    }

    public static function property($propertyName)
    {
        return array(new F_Property($propertyName), '__invoke');
    }

    public static function method($methodName, $args = array())
    {
        return array(new F_Method($methodName, $args), '__invoke');
    }

    public static function lambda($args, $body)
    {
        $key = md5($args . "\0". $body);

        if (array_key_exists($key, self::$lambdas) === false) {
            self::$lambdas[$key] = create_function($args, $body);
        }

        return self::$lambdas[$key];
    }

    public static function apply($fn, $args)
    {
        return call_user_func_array($fn, $args);
    }

    public static function assertCallable($fn)
    {
        if (is_callable($fn) === false) {
            throw new InvalidArgumentException('specified value is not callable');
        }
    }
}
