<?php


namespace Jazor;


class NameValueCollection implements \ArrayAccess
{

    private array $values = [];

    public function offsetExists($offset)
    {
        return isset($this->values[$offset]);
    }

    public function offsetGet($offset)
    {
        if (!isset($this->values[$offset])) return null;

        return implode(',', $this->values[$offset]);
    }

    public function offsetSet($offset, $value)
    {
        $this->values[$offset] = [$value];
    }

    public function offsetUnset($offset)
    {
        unset($this->values[$offset]);
    }

    public function getValues($name)
    {
        if (!isset($this->values[$name])) return null;
        return $this->values[$name];
    }

    public function add($name, $value)
    {

        if (!isset($this->values[$name])) {
            $this->values[$name] = [$value];
            return;
        }
        $this->values[$name][] = $value;
    }

    public function set($name, $value)
    {
        $this->offsetSet($name, $value);
    }

    public function remove($name)
    {
        $this->offsetUnset($name);
    }

    public function clear()
    {
        $this->values = [];
    }

    public function keys()
    {
        return array_keys($this->values);
    }

    /**
     * @param \Closure $cb
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function walk(\Closure $cb){

        $object = new \ReflectionObject($cb);
        $number = $object->getMethod('__invoke')->getNumberOfParameters();
        if($number < 1 || $number > 2) {
            throw new \Exception('arguments count error');
        }

        foreach ($this->values as $name => $values)
        {
            foreach ($values as $value){
                $number == 2 ? $cb($name, $value) : $cb($value);
            }
        }
    }

    /**
     * @param \Closure $cb
     * @param null $initial
     * @return mixed|null
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function reduce(\Closure $cb, $initial = null){

        $object = new \ReflectionObject($cb);
        $number = $object->getMethod('__invoke')->getNumberOfParameters();
        if($number < 2 || $number > 3) {
            throw new \Exception('arguments count error');
        }
        if($initial === null){
            $this->walk($cb);
            return null;
        }
        foreach ($this->values as $name => $values)
        {
            foreach ($values as $value){
                $initial = $number == 3 ? $cb($name, $value, $initial) : $cb($value, $initial) ;
            }
        }
        return $initial;
    }
}
