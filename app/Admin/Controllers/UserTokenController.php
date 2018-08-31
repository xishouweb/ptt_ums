<?php

namespace App\Admin\Controllers;

use App\Models\UserToken;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class UserTokenController extends Controller
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

            $content->header('user-tokens');
            $content->description('用户持有token的数量');

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

            $content->header('header');
            $content->description('description');

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
        return Admin::grid(UserToken::class, function (Grid $grid) {

            $grid->user_id('User_ID')->sortable();
            $grid->token_amount('token数量');
            $grid->token_type('token类型');
            $grid->freeze('已冻结数量');
            $grid->created_at('创建时间');
            $grid->updated_at('更新时间');
            $grid->disableCreateButton();
            $grid->disableActions();

            $grid->filter(function($filter){

                // 去掉默认的id过滤器
                $filter->disableIdFilter();

                // 在这里添加字段过滤器
                $filter->equal('user_id', 'User_ID');
                $filter->equal('token_type', 'token_type');

            });
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(UserToken::class, function (Form $form) {

            $form->display('user_id', 'User_ID');


            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
        });
    }

    /**
     * 禁用删除操作
     *
     * @return null
     */
    public function delete()
    {
        return null;
    }
}
