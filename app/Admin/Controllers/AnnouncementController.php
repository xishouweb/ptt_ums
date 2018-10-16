<?php

namespace App\Admin\Controllers;

use App\Models\Announcement;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AnnouncementController extends Controller
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
            return redirect("admin/wallet/announcement/$id/edit")
                ->withErrors($validator)
                ->withInput();
        }

        $announcement = Announcement::find($id);
        $announcement->title = $request->input('title');
        $announcement->title_en = $request->input('title_en');
        $announcement->url = $request->input('url');
        $announcement->save();

        return redirect('admin/wallet/announcement');
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|unique:announcements',
            'url' => 'required|unique:announcements',
        ], [
            'required' => ':attribute必须填写',
            'unique' => ':attribute数据库中已存在',
        ]);

        if ($validator->fails()) {
            return redirect('admin/wallet/announcement/create')
                ->withErrors($validator)
                ->withInput();
        }

        Announcement::create([
            'title'    => $request->input('title'),
            'title_en' => $request->input('title_en'),
            'url'      => $request->input('url'),
            'status'   => Announcement::ENABLED,
        ]);

        return redirect('admin/wallet/announcement');
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
        $announcement = Announcement::find($id);
        if ($announcement->status) {
            $announcement->status = Announcement::NOT_ENABLED;
        } else {
            $announcement->status = Announcement::ENABLED;
        }
        $announcement->save();

        return redirect('admin/wallet/announcement');
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(Announcement::class, function (Grid $grid) {
            $grid->model()->orderBy('status', 'desc')->orderBy('id', 'desc');
            $grid->id('ID')->sortable();
            $grid->column('title', '标题');
            $grid->column('title_en', '英文标题');
            $grid->column('status', '状态')->display(function ($status) {
                if ($status == Announcement::ENABLED) {
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
        return Admin::form(Announcement::class, function (Form $form) {
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
