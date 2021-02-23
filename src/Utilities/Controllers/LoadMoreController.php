<?php

namespace coreapi\Utilities\Controllers;

use Carbon\Carbon;
use coreapi\Utilities\Constants\Constant;
use coreapi\Utilities\Traits\LoadMoreTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class LoadMoreController extends JwtAuthController
{
    use LoadMoreTrait;

    /**
     * The request instance.
     *
     * @var Request
     */
    private $request;

    /**
     * Create a new controller instance.
     *
     * @param Request $request
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Get list with pagination
     *
     * @param int $sourceType 1 for page and 2 for load more
     * @param Model $filteredModel model that already filtered using custom filter
     * @param array $pagination pagination parameters
     *
     * @return array length or last_update and data
     */
    public function getList(int $sourceType, Model $filteredModel, array $pagination): array
    {
        if ($sourceType == Constant::TYPE_PAGINATION_PAGE) {
            return $this->getListPagination($filteredModel, $pagination);
        } elseif ($sourceType == Constant::TYPE_PAGINATION_LOAD_MORE) {
            return $this->getListLoadMore($filteredModel, $pagination);
        } else {
            return array();
        }
    }

    private function getListPagination($model, $pagination): array
    {
        return $this->paginateList($model, $pagination);
    }

    private function getListLoadMore($model, $pagination): array
    {
        $data = array();

        $topId = isset($pagination['top_id']) ? $pagination['top_id'] : 0;
        $bottomId = isset($pagination['bottom_id']) ? $pagination['bottom_id'] : 0;
        $topDbId = isset($pagination['top_db_id']) ? $pagination['top_db_id'] : 0;
        $bottomDbId = isset($pagination['bottom_db_id']) ? $pagination['bottom_db_id'] : 0;
        $count = isset($pagination['count']) ? $pagination['count'] : 0;
        $lastUpdate = isset($pagination['last_update']) ? $pagination['last_update'] : 0;

        $startDate = isset($pagination['start_date']) ? $pagination['start_date'] : "";
        $endDate = isset($pagination['end_date']) ? $pagination['end_date'] : "";
        $optionDate = isset($pagination['option_date']) ? $pagination['option_date'] : "";
        $searchKey = isset($pagination['search_key']) ? $pagination['search_key'] : "";
        $searchCol = isset($pagination['search_column']) ? $pagination['search_column'] : "";

        $addOnsParam = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'search_key' => $searchKey,
            'search_col' => $searchCol,
            'option_date' => $optionDate
        ];

        $topModel = clone $model;
        $bottomModel = clone $model;

        if ($topId != 0) {
            $dataTop = $this->paginateByTopId($topModel, $topId, $count, $addOnsParam);
            $data = $dataTop;
        }

        if ($bottomId != 0) {
            $dataBottom = $this->paginateByBottomId($bottomModel, $bottomId, $count, $addOnsParam);

            if (sizeof($data) == 0) {
                $data = $dataBottom;
            } else {
                $data = $data->merge($dataBottom);
            }
        }

        if ($lastUpdate != 0) {
            $dataUpdated = $this->paginateByLastUpdate($model, $topDbId, $bottomDbId, $lastUpdate, $addOnsParam);

            if (sizeof($data) == 0) {
                $data = $dataUpdated;
            } else {
                $data = $data->merge($dataUpdated);
            }
        }

        if ($topId == 0 && $bottomId == 0 && $lastUpdate == 0) {
            $data = $this->paginateFirst($model, $count, $addOnsParam);
        }

        $lastUpdated = Carbon::now('UTC')->timestamp;

        return [
            'last_update' => $lastUpdated,
            'data' => $data
        ];
    }

}
