<?php

namespace App\Admin\Controllers;

use App\Models\Notice;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NoticeController extends Controller
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
            $content->header('资讯列表');
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
            'title_en' => 'required',
            'url' => 'required',
        ], [
            'required' => ':attribute必须填写',
        ]);

        if ($validator->fails()) {
            return redirect("admin/wallet/notice/$id/edit")
                ->withErrors($validator)
                ->withInput();
        }

        $notice = Notice::find($id);
        $notice->title = $request->input('title');
        $notice->title_en = $request->input('title_en');
        $notice->url = $request->input('url');
        $notice->save();

        return redirect('admin/wallet/notice');
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|unique:notices',
            'title_en' => 'required|unique:notices',
            'url' => 'required|unique:notices',
        ], [
            'required' => ':attribute必须填写',
            'unique' => ':attribute数据库中已存在',
        ]);

        if ($validator->fails()) {
            return redirect('admin/wallet/notice/create')
                ->withErrors($validator)
                ->withInput();
        }

        Notice::create([
            'title'    => $request->input('title'),
            'title_en' => $request->input('title_en'),
            'url'      => $request->input('url'),
            'status'   => Notice::ENABLED,
        ]);

        return redirect('admin/wallet/notice');
    }

    public function createForm()
    {
        return Admin::content(function (Content $content) {
            $content->header('创建资讯');
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
        $notice = Notice::find($id);
        if ($notice->status) {
            $notice->status = Notice::NOT_ENABLED;
        } else {
            $notice->status = Notice::ENABLED;
        }
        $notice->save();

        return redirect('admin/wallet/notice');
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(Notice::class, function (Grid $grid) {
            $grid->model()->orderBy('status', 'desc')->orderBy('id', 'desc');
            $grid->id('ID')->sortable();
            $grid->column('title', '标题');
            $grid->column('title_en', '英文标题');
            $grid->column('status', '状态')->display(function ($status) {
                if ($status == Notice::ENABLED) {
                    return "<span class='label label-success'>启用</span>";
                }
                return "<span class='label label-danger'>未启用</span>";
            });
            $grid->column('url', '链接地址')->display(function ($url) {
                return "<a href='$url' target='_blank'>点击跳转</a>";
            });
            $grid->column('created_at', '创建时间');
//            $grid->actions(function ($actions) {
//                // prepend一个操作
//                $actions->prepend('<a href="">切换状态</a>');
//            });
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(Notice::class, function (Form $form) {
            $form->display('id', 'ID');
            //todo rules不起作用
            $form->text('title', '标题')->rules('required', [
                'required' => '标题必须填写',
            ]);
            $form->text('title_en', '英文标题');
            $form->text('url', '链接')->rules('required', [
                'required' => '链接必须填写',
            ]);
            $form->display('created_at', '创建时间');
            $form->display('updated_at', '修改时间');
        });
    }
}
