<?php

namespace App\Admin\Controllers;

use App\Models\Banner;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BannerController extends Controller
{
    use ModelForm;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {
            $content->header('公告列表');
            $content->body($this->grid());
        });
    }

    /**
     * Edit interface.
     *
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {
            $content->header('header');
            $content->description('description');
            $content->body($this->form()->edit($id));
        });
    }

    /**
     * Update interface.
     *
     * @return Content
     */
    public function update($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'url' => 'required',
        ], [
            'required' => ':attribute必须填写',
        ]);

        if ($validator->fails()) {
            return redirect('admin/wallet/Banner/create')
                ->withErrors($validator)
                ->withInput();
        }

        $Banner = Banner::find($id);
        $Banner->title = $request->input('title');
        $Banner->url = $request->input('url');
        $Banner->save();

        return redirect('admin/wallet/Banner');
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|unique:Banners',
            'url' => 'required|unique:Banners',
        ], [
            'required' => ':attribute必须填写',
            'unique' => ':attribute数据库中已存在',
        ]);

        if ($validator->fails()) {
            return redirect('admin/wallet/Banner/create')
                ->withErrors($validator)
                ->withInput();
        }

        Banner::create([
            'title'  => $request->input('title'),
            'url'    => $request->input('url'),
            'status' => Banner::ENABLED,
        ]);
    }

    public function createForm()
    {
        return Admin::content(function (Content $content) {
            $content->header('创建公告');
            $content->body($this->form());
        });
    }

    /**
     * Delete interface.
     *
     * @return Content
     */
    public function delete($id)
    {
        $Banner = Banner::find($id);
        if ($Banner->status) {
            $Banner->status = Banner::NOT_ENABLED;
        } else {
            $Banner->status = Banner::ENABLED;
        }
        $Banner->save();
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(Banner::class, function (Grid $grid) {
            $grid->model()->orderBy('id', 'desc');
            $grid->id('ID')->sortable();
            $grid->column('title', '标题');
            $grid->column('status', '状态')->display(function ($status) {
                if ($status == Banner::ENABLED) {
                    return "<span class='label label-success'>启用</span>";
                }
                return "<span class='label label-danger'>未启用</span>";
            });
            $grid->column('content', '链接地址');
            $grid->column('created_at', '创建时间');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
//         'image', 'sort',
        return Admin::form(Banner::class, function (Form $form) {
            $form->display('id', 'ID');
            //todo rules不起作用
            $form->text('title', '标题')->rules('required', [
                'required' => '标题必须填写',
            ]);
            $form->image('image', '图片');
            $form->text('content', '内容（如：链接）')->rules('required', [
                'required' => '内容必须填写',
            ]);
            $form->radio('type', '类型')->options(['m' => 'Female', 'f'=> 'Male'])->rules('required', [
                'required' => '类型必须填写',
            ]);;
            $form->text('sort', '排序')->rules('required', [
                'required' => '排序必须填写',
            ]);
            $form->display('created_at', '创建时间');
            $form->display('updated_at', '修改时间');
        });
    }
}
