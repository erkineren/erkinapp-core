<?php

if (!function_exists('renderDatatableResult')) {
    function renderDatatableResult(\Envms\FluentPDO\Queries\Select $q, \ErkinApp\Controller $controller, Callable $record_callback = null)
    {
        $recordsTotal = $q->count();

        $columns = $q->getRawStatements()['SELECT'][0];
        $columns = str_replace('SQL_CALC_FOUND_ROWS', '', $columns);
        $columns = explode(',', $columns);
        $columns = array_map('trim', $columns);

        $search = $controller->_post('search');
        if ($search && $search['value']) {
            foreach ($columns as $column) {
                $q->whereOr("$column LIKE '%{$search['value']}%'");
            }
        }

        if ($order = $controller->_post('order')) {
            if (isset($columns[$order[0]['column']])) {
                $column = $columns[$order[0]['column']];
                $q->orderBy("{$column} {$order[0]['dir']}");
            }
        }

        $q = $q->limit($controller->_post('length'))->offset($controller->_post('start'));
//            _yaz($q->getRawStatements());
        $result = $q->fetchAll();

        $result = array_map(function ($a) use ($record_callback) {
            if ($record_callback) $a = $record_callback($a);
            return array_values($a);
        }, $result);


        return [
            'draw' => $controller->_post('draw', 0),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $controller->db->getPdo()->query("SELECT FOUND_ROWS()")->fetchColumn(),
            'data' => $result
        ];
    }
}


