<?php
/**
 * Created by PhpStorm.
 * User: Raihan Fajri
 * Date: 14/05/2019
 * Time: 17:18
 */

namespace coreapi\Utilities\Traits;

use coreapi\Utilities\Constants\Constant;
use coreapi\Utilities\Helpers\StringHelper;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

Trait LoadMoreTrait {

    public static function paginateList($model, $pagination)
    {
        $table = $model->getModel()->getTable() . ".";

        $page = isset($pagination['page']) ? (int) $pagination['page'] : 0;
        $limit = isset($pagination['limit']) ? (int) $pagination['limit'] : 0;
        $column = isset($pagination['column']) ? (string) $pagination['column'] : "";
        $ascending = isset($pagination['ascending']) ? (bool) $pagination['ascending'] : false;
        $query = isset($pagination['query']) ? (string) $pagination['query'] : "";
        $queryCol = isset($pagination['query_column']) ? (string) $pagination['query_column'] : "";

        $model = self::filterPaginationBySearch($model, $table, $query, $queryCol);

        $count = clone $model->getQuery();

        if (!empty($count->groups)) {
            $count = $count->select(DB::raw('count(distinct('. $count->groups[0] .')) as total'));
            $count->groups = null;
            $count = (int) $count->first()->total;
        }
        else {
            $count = $count->count();
        }
 
        if (!empty($column)) {
            if ($ascending) { $groupBy = 'ASC'; } else { $groupBy = 'DESC'; }
            $model = $model->orderBy($table . $column, $groupBy);
        }

        if ($limit > 0 && $page > 0) {
            $offset = ($page - 1) * $limit;
            $model = $model->offset($offset)->limit($limit);
        }

        return [
            'length' => $count,
            'data' => $model->get()
        ];
    }

    public static function paginateByTopId($model, $topId, $count, $addOnsParam = array())
    {
        $table = $model->getModel()->getTable() . ".";
        $model = $model->where($table . 'id', '>', $topId);

        $startDate = isset($addOnsParam['start_date']) ? $addOnsParam['start_date'] : "";
        $endDate = isset($addOnsParam['end_date']) ? $addOnsParam['end_date'] : "";
        $optionDate = isset($addOnsParam['option_date']) ? $addOnsParam['option_date'] : "";

        $searchKey = isset($addOnsParam['search_key']) ? $addOnsParam['search_key'] : "";
        $searchCol = isset($addOnsParam['search_col']) ? $addOnsParam['search_col'] : "";

        $model = self::filterPaginationByDate($model, $table, $optionDate, $startDate, $endDate);

        $model = self::filterPaginationBySearch($model, $table, $searchKey, $searchCol);

        $model = $model->orderBy($table . 'id', 'desc');

        if ($count != 0) {
            $model = $model->limit($count);
        }

        return $model->get();
    }

    public static function paginateByBottomId($model, $bottomId, $count, $addOnsParam)
    {
        $table = $model->getModel()->getTable() . ".";
        $model = $model->where($table . 'id', '<', $bottomId);

        $startDate = isset($addOnsParam['start_date']) ? $addOnsParam['start_date'] : "";
        $endDate = isset($addOnsParam['end_date']) ? $addOnsParam['end_date'] : "";
        $optionDate = isset($addOnsParam['option_date']) ? $addOnsParam['option_date'] : "";

        $searchKey = isset($addOnsParam['search_key']) ? $addOnsParam['search_key'] : "";
        $searchCol = isset($addOnsParam['search_col']) ? $addOnsParam['search_col'] : "";

        $model = self::filterPaginationByDate($model, $table, $optionDate, $startDate, $endDate);

        $model = self::filterPaginationBySearch($model, $table, $searchKey, $searchCol);

        $model = $model->orderBy($table . 'id', 'desc');

        if ($count != 0) {
            $model = $model->limit($count);
        }

        return $model->get();
    }

    public static function paginateByLastUpdate($model, $topDbId, $bottomDbId, $lastUpdateTime, $addOnsParam)
    {
        $table =$model->getModel()->getTable() . ".";
        $lastUpdate = \Carbon\Carbon::createFromTimestamp($lastUpdateTime, 'Asia/Jakarta');

        $model = $model->where($table . 'updated_date', $lastUpdate)
            ->where($table . 'id', '>', $bottomDbId)
            ->where($table . 'id', '<', $topDbId);

        $startDate = isset($addOnsParam['start_date']) ? $addOnsParam['start_date'] : "";
        $endDate = isset($addOnsParam['end_date']) ? $addOnsParam['end_date'] : "";
        $optionDate = isset($addOnsParam['option_date']) ? $addOnsParam['option_date'] : "";

        $searchKey = isset($addOnsParam['search_key']) ? $addOnsParam['search_key'] : "";
        $searchCol = isset($addOnsParam['search_col']) ? $addOnsParam['search_col'] : "";

        $model = self::filterPaginationByDate($model, $table, $optionDate, $startDate, $endDate);

        $model = self::filterPaginationBySearch($model, $table, $searchKey, $searchCol);

        $model = $model->orderBy($table . 'id', 'desc');

        return $model->get();
    }

    public static function paginateFirst($model, $count, $addOnsParam)
    {
        $table = $model->getModel()->getTable() . ".";

        $startDate = isset($addOnsParam['start_date']) ? $addOnsParam['start_date'] : "";
        $endDate = isset($addOnsParam['end_date']) ? $addOnsParam['end_date'] : "";
        $optionDate = isset($addOnsParam['option_date']) ? $addOnsParam['option_date'] : "";

        $searchKey = isset($addOnsParam['search_key']) ? $addOnsParam['search_key'] : "";
        $searchCol = isset($addOnsParam['search_col']) ? $addOnsParam['search_col'] : "";

        $model = self::filterPaginationByDate($model, $table, $optionDate, $startDate, $endDate);

        $model = self::filterPaginationBySearch($model, $table, $searchKey, $searchCol);

        $model = $model->orderBy($table . 'id', 'desc');

        if ($count != 0) {
            $model = $model->limit($count);
        }

        return $model->get();
    }

    private static function filterPaginationByDate($model, $table, $optionDate, $startDate, $endDate)
    {
        $columnExist = self::isColumnOnTableExist($table, $optionDate);
        
        if ($columnExist && !empty($startDate) && !empty($endDate)) {
            $model = $model->where($table .$optionDate, '>=', $startDate)
                ->where($table . $optionDate, '<=', $endDate);
        }

        return $model;
    }

    private static function filterPaginationBySearch($model, $table, $searchKey, $searchCol) 
    {
        if (empty($searchCol)) {
            return $model;
        }

        $columnExist = self::isColumnOnTableExist($table, $searchCol);

        if ($columnExist && strlen($searchKey) > 2) {
            $query = StringHelper::convertToUtf8($searchKey);

            $model = $model->where($table . $searchCol, 'like', '%' . $query . '%');
        }

        return $model;
    }

    private static function isColumnOnTableExist($table, $columnName)
    {
        $table = str_replace('.', '', $table);

        return Schema::hasColumn($table, $columnName);
    }

}