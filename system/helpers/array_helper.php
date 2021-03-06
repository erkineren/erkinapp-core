<?php

namespace ErkinApp\Helpers {
    /**
     * Element
     *
     * Lets you determine whether an array index is set and whether it has a value.
     * If the element is empty it returns NULL (or whatever you specify as the default value.)
     *
     * @param string
     * @param array
     * @param mixed
     * @return    mixed    depends on what the array contains
     */
    function element($item, array $array, $default = NULL)
    {
        return array_key_exists($item, $array) ? $array[$item] : $default;
    }

    /**
     * Random Element - Takes an array as input and returns a random element
     *
     * @param array
     * @return    mixed    depends on what the array contains
     */
    function randomElement($array)
    {
        return is_array($array) ? $array[array_rand($array)] : $array;
    }

    /**
     * Elements
     *
     * Returns only the array items specified. Will return a default value if
     * it is not set.
     *
     * @param array
     * @param array
     * @param mixed
     * @return    mixed    depends on what the array contains
     */
    function elements($items, array $array, $default = NULL)
    {
        $return = array();

        is_array($items) OR $items = array($items);

        foreach ($items as $item) {
            $return[$item] = array_key_exists($item, $array) ? $array[$item] : $default;
        }

        return $return;
    }

    function arrayWhereInStr($arr, $key_name = null)
    {
        if ($key_name != null)
            $filtered = array_column($arr, $key_name);
        else
            $filtered = $arr;
        $str = "('";
        if (!empty($filtered)) {
            $str .= implode("','", $filtered);
        }
        $str .= "')";
        return $str;
    }

    function makeInStr($in_data)
    {
        $in_str = $in_data;
        if (is_array($in_data)) $in_str = implode("','", $in_data);
        else return makeInStr(explode(',', $in_data));
        return "'" . $in_str . "'";
    }

    function arraySelectColumns($arr, $columns)
    {
        return array_map(
            function ($a) use ($columns) {
                return array_intersect_key($a, array_flip($columns));
            },
            $arr
        );
    }

    function arraySelectInnerColumns($arr, $column)
    {
        return array_map(
            function ($a) use ($column) {
                return $a[$column];
            },
            $arr
        );
    }

    function arrayUnselectColumnsMulti($arr, $columns)
    {
        return array_map(
            function ($a) use ($columns) {
                return array_diff_key($a, array_flip($columns));
            },
            $arr
        );
    }

    function arrayUnselectColumns($arr, $columns)
    {
        return array_diff_key($arr, array_flip($columns));
    }

    function arraySort($array, $on, $order = SORT_ASC)
    {

        $new_array = array();
        $sortable_array = array();

        if (count($array) > 0) {
            foreach ($array as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $k2 => $v2) {
                        if ($k2 == $on) {
                            $sortable_array[$k] = $v2;
                        }
                    }
                } else {
                    $sortable_array[$k] = $v;
                }
            }

            switch ($order) {
                case SORT_ASC:
                    asort($sortable_array);
                    break;
                case SORT_DESC:
                    arsort($sortable_array);
                    break;
            }

            foreach ($sortable_array as $k => $v) {
                $new_array[$k] = $array[$k];
            }
        }

        return $new_array;
    }

    function compareArray($a, $b, $column)
    {
        $a_c = array_column($a, $column);
        $b_c = array_column($b, $column);

        $diff = array_diff($a_c, $b_c);

        $data = [];
        foreach ($diff as $key => $item) {
            $data[$key] = $a[$key];
        }

        return $data;
    }

    function arrayIntersect($a, $b, $column)
    {
        $a_c = array_column($a, $column);
        $b_c = array_column($b, $column);

        $intersect = array_intersect($a_c, $b_c);

        $data = [];
        foreach ($intersect as $key => $item) {
            $data[$key] = $a[$key];
        }

        return $data;
    }

    function arrayMakeColumnKey($array, $column, $remove_column = false)
    {
        $result = [];
        foreach ($array as $key => $value) {
            $result[$value[$column]] = $value;
            if ($remove_column) {
                unset($result[$value[$column]][$column]);
            }
        }

        return $result;
    }

    function arrayMakeKeyValuePair($array, $key_column, $value_column, $group = false)
    {
        $result = [];
        foreach ($array as $key => $value) {
            if ($group)
                $result[$value[$key_column]][] = $value[$value_column];
            else
                $result[$value[$key_column]] = $value[$value_column];
        }

        return $result;
    }

    function arraySearchMulti($needle, array $haystack)
    {
        $result_keys = [];
        foreach ($haystack as $key => $item) {
            if ($item == $needle)
                $result_keys[] = $key;
        }

        return $result_keys;
    }
}