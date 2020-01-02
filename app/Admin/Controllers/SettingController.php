<?php

namespace App\Admin\Controllers;

use App\Models\Setting;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class SettingController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'App\Models\Setting';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Setting);

        $grid->model()->orderBy('id', 'desc');

        $grid->name()->editable();
        $grid->key()->editable();
        $grid->value()->editable();
        $grid->payloda('备注')->editable();
        $grid->module('位置')->editable();
        
        $grid->column('created_at', '创建时间');

        $grid->filter(function($filter){

            // 在这里添加字段过滤器
            $filter->like('name', '活动名称');
            $filter->equal('id', '活动编号');
            $filter->equal('key');
        });

        $grid->disableExport();
        $grid->disableColumnSelector();
        $grid->disableActions();
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Setting::findOrFail($id));

        $show->panel()
        ->title('全局设置')
        ->tools(function ($tools) {
            $tools->disableDelete();
        });

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Setting);

        $form->text('name');
        $form->text('key');
        $form->text('value');
        $form->text('module', '位置');
        $form->text('payload', '备注');

        $form->footer(function ($footer) {
        
            // 去掉`查看`checkbox
            $footer->disableViewCheck();
        
            // 去掉`继续编辑`checkbox
            $footer->disableEditingCheck();
        
            // 去掉`继续创建`checkbox
            $footer->disableCreatingCheck();
        
        });

        return $form;
    }
}
