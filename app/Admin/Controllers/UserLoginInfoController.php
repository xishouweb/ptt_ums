<?php

namespace App\Admin\Controllers;

use App\Models\UserLoginInfo;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;
use App\Models\UserWalletBalance;

class UserLoginInfoController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '登录记录';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid($user_id)
    {
        $grid = new Grid(new UserLoginInfo);

        $grid->model()->whereUserId($user_id)->orderBy('id', 'desc');

        $grid->id('ID');

        $grid->column('设备名称')->display(function () {
            return $this->device_name;
        });

        $grid->column('IP')->display(function () {
            return $this->ip;
        });

        $grid->column('User-Agent')->display(function () {
            return $this->user_agent;
        });

        $grid->column('其他信息')->display(function () {
            return $this->info;
        });

        $grid->column('时间')->display(function () {
            return date($this->created_at);
        });

        $grid->filter(function($filter){
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            // 设置datetime类型
            $filter->between('created_at', '时间段')->datetime();
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
            ['text' => '登录记录']
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
}
