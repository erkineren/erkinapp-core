<?php


namespace ErkinApp\Components;


use RecursiveArrayIterator;
use RecursiveIteratorIterator;

class Config extends ArrayAndObjectAccess
{
    public function getAsDotNotation($appendKey = '')
    {
        $recursiveIteratorIterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($this->items));
        $result = [];
        foreach ($recursiveIteratorIterator as $leafValue) {
            $keys = array();
            foreach (range(0, $recursiveIteratorIterator->getDepth()) as $depth) {
                $keys[] = $recursiveIteratorIterator->getSubIterator($depth)->key();
            }

            $result[$appendKey . join('.', $keys)] = $leafValue;
        }
        return $result;
    }
}