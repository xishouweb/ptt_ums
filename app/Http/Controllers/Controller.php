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

    public function paginate($builder, $total_size = null, $page = null,$page_size = null)
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
            'data'    => $data,
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

    public function format_list($data, $from = [])
    {
        $result = [];
        foreach($data as $d) {
            $result[] = $d->format($from);
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
    public function apiResponse($data = [], $message = '操作成功', $code = 0)
    {
        return response()->json(['code' => $code, 'message' => $message, 'data' => $data]);
    }



}
