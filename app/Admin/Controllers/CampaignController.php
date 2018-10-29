<?php

namespace App\Admin\Controllers;

use App\Models\Campaign;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class CampaignController extends Controller
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

            $content->header('活动列表');

            $content->body($this->grid());
        });
    }

    /**
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {

            $content->header('编辑活动信息');

            $content->body($this->form()->edit($id));
        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {

            $content->header('header');
            $content->description('description');

            $content->body($this->form());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(Campaign::class, function (Grid $grid) {

            $grid->id('ID')->sortable();
            $grid->column('title', '标题');
            $grid->column('desc', '描述');
            $grid->column('status', '状态')->display(function ($status) {
                if ($status == Campaign::STATUS_NORMAL) {
                    return "<span class='label label-success'>启用</span>";
                }
                return "<span class='label label-danger'>未启用</span>";
            });

            $grid->start_date('开始时间');
            $grid->end_date('结束时间');
            $grid->column('created_at', '创建时间');
            $grid->column('updated_at', '更新时间');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(Campaign::class, function (Form $form) {

            $form->display('id', 'ID');
            $form->text('title', '标题')->rules('required', [
                'required' => '标题必须填写',
            ]);
            $form->text('desc', '描述');
            $form->select('status', '状态')->options([0 => '下架', 1 => '启用']);

            $form->date('start_date', '开始时间')->format('YYYY-MM-DD');
            $form->date('end_date',  '结束时间')->format('YYYY-MM-DD');
            $form->display('created_at', '创建时间');
            $form->display('updated_at', '修改时间');
        });
    }
}
