<?php

namespace App\Http\Controllers;

use App\Models\FormatInterface;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $page       = 1;
    protected $page_size  = 10;
    protected $total_size = 100;

    public function paginate($builder, $form = [], $total_size = null, $page = null,$page_size = null)
    {
        if (request()->get('page')) {
            $this->page = (int) request()->get('page');
        } elseif ($page) {
            $this->page = $page;
        }

        if (request()->get('page_size')) {
            $this->page_size = (int) request()->get('page_size');
        } elseif ($page_size) {
            $this->page_size = $page_size;
        }

        if ($total_size === null) {
            $this->total_size = $builder->count();
        } else {
            $this->total_size = $total_size;
        }

        $data = $builder->skip(($this->page - 1) * $this->page_size)->take($this->page_size)->get();
        $responseData = [
            'data'    => $this->format_list($data, $form),
            'page'    => $this->page,
            'page_size'    => $this->page_size,
            'total_size'    => $this->total_size,
        ];
        return $responseData;
    }

    public function format(FormatInterface $format)
    {
        return $format->format();
    }

    public function format_list($data, $form = [])
    {
        $result = [];
        foreach($data as $d) {
            $result[] = $d->format($form);
        }
        return $result;
    }

    /**
     * Standardize response
     *
     * @param string|array $data
     * @param string       $message
     * @param int          $code
     * @return mixed
     */
    public function apiResponse($data = [], $message = '操作成功', $code = 1)
    {
        return response()->json(['code' => $code, 'message' => $message, 'data' => $data]);
    }


    protected function _not_found_json($resource)
    {
        $json = array(
            "error" => $resource . " 不存在",
        );
        return response()->json($json, 404);
    }

    protected function _forbidden_json()
    {
        $json = array(
            "error" => "权限失败",
        );
        return response()->json($json, 403);
    }

    protected function _bad_json($msg, $code = 0)
    {
        $json = array(
            'code' => $code,
            "error" => $msg,
        );
        return response()->json($json, 400);
    }

    protected function _success_json($data = [], $msg = '操作成功', $code = 1)
    {
        $json = array(
            'data' => $data,
            "msg" => $msg,
            'code' => $code,
        );
        return response()->json($json, 200);
    }

    public function success($message = '操作成功', $code = 1)
    {
        return $this->apiResponse(null, $message, $code);
    }

    public function error($message = '操作失败', $code = 0)
    {
        return $this->apiResponse(null, $message, $code);

    }

    public function response($data = [])
    {
        return response()->json($data);
    }
}
