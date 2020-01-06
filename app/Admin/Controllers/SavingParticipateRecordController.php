<?php

namespace App\Admin\Controllers;

use App\Models\SavingParticipateRecord;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;

class SavingParticipateRecordController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'App\Models\SavingParticipateRecord';

      /**
     * Index interface.
     *
     * @return Content
     */
    public function index(Content $content)
    {
        return $content->header('持仓管理')
                ->breadcrumb(
                    ['text' => '持仓用户列表']
                )
                ->body($this->grid());
    }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SavingParticipateRecord);

        $grid->model()->orderBy('id', 'desc');
        $grid->column('user_id', '用户ID');
        $grid->column('手机号')->display(function () {
            return $this->user['phone'];
        });
        $grid->column('用户昵称')->display(function () {
            return $this->user['nickname'];
        });
        $grid->column('云端钱包可余额(PTT)')->display(function () {
            $balance = $this->user->userWalletBalances->where('symbol', 'ptt');
            // dd($balance);
            return number_format($balance[0]['total_balance'] - $balance[0]['locked_balance'], 4);
        });

        $grid->column('累计持仓天数')->display(function () {
            return count($this->savingAwards());
        });

        $grid->column('累计累计获得收益')->display(function () {
            return  number_format($this->savingAwards()->sum('award'), 4);
        });

        $grid->column('saving_id', '活动编号');

        $grid->column('持仓记录')->display(function () {
            return "<a href='/admin/wallet/saving-awards/$this->user_id' target='_blank'>查看</a>";
        });

        $grid->filter(function($filter){

            // 去掉默认的id过滤器
            $filter->disableIdFilter();

            // 在这里添加字段过滤器
            $filter->equal('user_id', '用户ID');
           
            $filter->equal('saving_id', '活动编号');
         
        });
        $grid->tools(function ($tools) {
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });
        });
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
        $show = new Show(SavingParticipateRecord::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('user_id', __('User id'));
        $show->field('saving_id', __('Saving id'));
        $show->field('status', __('Status'));
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
        $form = new Form(new SavingParticipateRecord);

        $form->number('user_id', __('User id'));
        $form->number('saving_id', __('Saving id'));
        $form->number('status', __('Status'));

        return $form;
    }
}
