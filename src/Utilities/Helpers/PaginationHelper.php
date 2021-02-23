<?php

namespace coreapi\Utilities\Helpers;

use Illuminate\Http\Request;

class PaginationHelper {

    const KEY_LIMIT = 'limit';
    const KEY_PAGE = 'page';

    public static function handleError(Request $request, $maxLimit = 100): \stdClass
    {
        $response = new \stdClass();
        $response->error = false;
        $response->code = '';
        $response->message = '';

        $pagination = $request->input('pagination');
        if (empty($pagination)) {
            $response->error = true;
            $response->code = 'PAGINATION.001';
            $response->message = 'Parameters pagination not found, please try again';
            return $response;
        }

        if (!isset($pagination[self::KEY_PAGE]) || !isset($pagination[self::KEY_LIMIT])) {
            $response->error = true;
            $response->code = 'PAGINATION.002';
            $response->message = 'Parameters pagination invalid, please try again';
            return $response;
        }

        $page = (int) $pagination[self::KEY_PAGE];
        if (empty($page) || $page == 0) {
            $response->error = true;
            $response->code = 'PAGINATION.003';
            $response->message = 'Page invalid, please try again';
            return $response;
        }

        $limit = (int) $pagination[self::KEY_LIMIT];
        if (empty($limit) || $limit == 0) {
            $response->error = true;
            $response->code = 'PAGINATION.004';
            $response->message = 'Limit invalid, please try again';
            return $response;
        }

        $limit = (int) $pagination[self::KEY_LIMIT];
        if ($limit > $maxLimit) {
            $response->error = true;
            $response->code = 'PAGINATION.005';
            $response->message = 'This request exceeds the maximum limit. the maximum data request is '.$maxLimit.' records, please try again';
            return $response;
        }

        return $response;

    }
}