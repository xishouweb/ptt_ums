<?php

namespace App\Admin\Controllers;

use App\Models\UserWalletTransaction;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;
use Encore\Admin\Facades\Admin;
use App\Models\UserWalletBalance;

class UserWalletTransactionController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'App\Models\UserWalletTransaction';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid($user_id)
    {
        $grid = new Grid(new UserWalletTransaction); 

        $grid->model()->whereUserId($user_id)->orderBy('id', 'desc');

        $grid->id('交易单号')->display(function ($id) {
            return "<a href='/admin/wallet/user-wallet-balances/$id' target='_blank'>$id</a>";
        });

        $grid->column('type', '类型')->display(function ($type) {
            if ($type == 1) {
                $text = "转入";
            } elseif ($type == 2) {
                $text = "提币";
            } elseif ($type == 3) {
                $text = "收益";
            }
            return $text;
        });

        $grid->column('status', '交易状态')->display(function ($status) {
            if ($status === 0) {
                return "<span class='label label-warning'>区块确认中</span>";
            } elseif ($status === 1) {
                return "<span class='label label-success'>完成</span>";
            } elseif ($status === 2) {
                return "<span class='label label-primary'>申请中</span>";
            } elseif ($status === 3) {
                return "<span class='label label-info'>转账中</span>";
            } elseif ($status === 4) {
                return "<span class='label label-default'>申请未通过</span>";
            }
           
        });
        
        $grid->amount('数量')->display(function ($amount) {
            return number_format($amount);
        });
        $grid->fee('手续费')->display(function ($fee) {
            return number_format($fee);
        });

        $grid->column('交易时间')->display(function () {
            return "开始时间: $this->created_at </br> 结束时间: $this->completed_at";
        });

        $grid->column('地址')->display(function () {
            if($this->type == 2) {
                return "To: <a href='https://etherscan.io/address/$this->to' target='_blank'>$this->to</a>";
            }
            
            return "From: <a href='https://etherscan.io/address/$this->from' target='_blank'>$this->from</a>";
        });

        $grid->filter(function($filter){

            // 去掉默认的id过滤器
            $filter->disableIdFilter();

            // 在这里添加字段过滤器
            $filter->equal('type')->radio([
                ''   => '全部记录',
                1    => '转入记录',
                2    => '提币记录',
                3    => '收益记录',
            ]);;
        });
      
        $grid->disableCreateButton();
        $grid->disableActions();
        $grid->disableExport();
        $grid->disableColumnSelector();
        $grid->batchActions(function ($batch) {
            $batch->disableDelete();
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    public function show($user_id, Content $content)
    {
        $user = UserWalletBalance::leftJoin('users', 'user_wallet_balances.user_id', '=', 'users.id')
            ->where('user_wallet_balances.user_id', $user_id)
            ->select('user_wallet_balances.*', 'nickname', 'avatar', 'phone')
            ->first();

        return $content
        ->header('交易记录')
        ->breadcrumb(
            ['text' => '用户列表', 'url' => '/wallet/user-wallet-balances'],
            ['text' => '交易记录']
        )
        ->row("<div class='panel panel-default'>
                    <div class='panel-body container'>
                        <div class='row'>
                            <div class='col-xs-12 col-md-3'><h4>用户ID: $user_id</h4></div>
                            <div class='col-xs-12 col-md-3'><h4>用户昵称: $user->nickname</h4></div>
                            <div class='col-xs-12 col-md-6'><h4>钱包地址: <a href='https://etherscan.io/address/$user->address' target='_blank'>$user->address</a></h4></div>
                        </div>
                        <div class='row'>
                            <div class='col-xs-12 col-md-3'><h4>注册手机: $user->phone</h4></div>
                            <div class='col-xs-12 col-md-3'><h4>钱包余额: " . number_format($user->total_balance) . "</h4></div>
                        </div>
                    </div>
                </div>"
        )
        ->body($this->grid($user_id));
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new UserWalletTransaction);

        $form->number('user_id', __('User id'));
        $form->text('address', __('Address'));
        $form->text('symbol', __('Symbol'));
        $form->number('type', __('Type'));
        $form->number('status', __('Status'));
        $form->decimal('amount', __('Amount'));
        $form->text('to', __('To'));
        $form->text('from', __('From'));
        $form->decimal('fee', __('Fee'));
        $form->text('tx_hash', __('Tx hash'));
        $form->number('block_number', __('Block number'));
        $form->number('block_confirm', __('Block confirm'));
        $form->text('rate', __('Rate'));
        $form->text('remark', __('Remark'));
        $form->datetime('completed_at', __('Completed at'))->default(date('Y-m-d H:i:s'));

        return $form;
    }
}
