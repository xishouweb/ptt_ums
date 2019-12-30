<?php

namespace App\Admin\Controllers;

use App\User;
use App\Models\SavingAward;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;

class SavingAwardController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'App\Models\SavingAward';

     /**
     * Index interface.
     *
     * @return Content
     */
    public function index(Content $content)
    {
        return $content->header('活动管理')
                ->breadcrumb(
                    ['text' => '持仓用户列表', 'url' => '/wallet/saving-participate-records'],
                    ['text' => '持仓记录']
                )
                ->body($this->grid());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid($user_id)
    {
        $grid = new Grid(new SavingAward);

        $grid->model()->whereUserId($user_id)->orderBy('id', 'desc');

        $grid->column('created_at', '持仓日期')->display(function($created_at){
            return date('Y-m-d', strtotime($created_at));
        });
        $grid->column('amount', '持仓数量')->display(function($amount){
            return number_format($amount);
        });

        $grid->column('年化收益率')->display(function () {
            return $this->savings['rate'] * 100 . '%';
        });

        $grid->column('award', '当日获得收益')->display(function($award){
            return number_format($award);
        });
        $grid->column('saving_id', '持仓活动编号');
        
        $grid->column('持仓活动名称')->display(function () {
            return $this->savings['title'];
        });

        $grid->disableFilter();
        $grid->disableCreateButton();
        $grid->disableActions();
        $grid->disableExport();
        $grid->disableColumnSelector();
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
        $show = new Show(SavingAward::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('user_id', __('User id'));
        $show->field('saving_id', __('Saving id'));
        $show->field('amount', __('Amount'));
        $show->field('award', __('Award'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('deleted_at', __('Deleted at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new SavingAward);

        $form->number('user_id', __('User id'));
        $form->number('saving_id', __('Saving id'));
        $form->decimal('amount', __('Amount'));
        $form->decimal('award', __('Award'));

        return $form;
    }

    public function show($user_id, Content $content)
    {
        $user = User::findOrFail($user_id);
        $content->header('活动管理')
            ->breadcrumb(
                ['text' => '持仓用户列表', 'url' => '/wallet/saving-participate-records'],
                ['text' => '持仓记录']
            )
            ->row("<div class='panel panel-default'>
                        <div class='container panel-body'>
                            <div class='row'>
                                <div class='col-xs-3'>
                                    <h4>用户ID : $user->id</h4>
                                </div>
                                <div class='col-xs-3'>
                                    <h4>手机号 : $user->phone</h4>
                                </div>
                                <div class='col-xs-6'>
                                    <h4>云端钱包地址 : <a href='https://etherscan.io/address/$user->cloud_wallet_address' target='_blank'>$user->cloud_wallet_address</a></h4>
                                </div>
                            </div>
                        </div>
                    </div>"
            )
            ->body($this->grid($user_id));

        

        return $content;
    }
}
