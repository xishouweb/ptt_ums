<?php

namespace App\Admin\Controllers;

use App\Models\UserWalletWithdrawal;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;
use Encore\Admin\Facades\Admin;

class UserWalletWithdrawalController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'App\Models\UserWalletWithdrawal';

          /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('提币申请')
            ->breadcrumb(
                ['text' => '提币申请']
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
        $grid = new Grid(new UserWalletWithdrawal);

        $grid->model()->orderBy('status')->orderBy('id', 'desc');

        $grid->column('id', '提币订单号')->display(function ($id) {
            return "<a href='/admin/wallet/user-wallet-withdrawals/$id' target='_blank'>$id</a>";
        });

        $grid->column('user_id', '用户ID')->display(function ($user_id) {
            return "<a href='/admin/wallet/user-wallet-balances/$user_id' target='_blank'>$user_id</a>";
        });

        $grid->column('提币手机号')->display(function () {
            return $this->users['phone'];
        });
        
        $grid->column('昵称')->display(function () {
            return $this->users['nickname'];
        });

        $grid->column('云端钱包可用余额(PTT)')->display(function () {
            return $this->getBalanceOf('ptt');
        });

        $grid->column('amount', '提币数量');
        $grid->column('created_at', '提币时间');

        $grid->column('status', '状态')->display(function ($status) {
            if ($status == UserWalletWithdrawal::PENDING_STATUS) {
                return "<span class='label label-warning'>申请中</span>";
            } elseif ($status == UserWalletWithdrawal::COMPLETE_STATUS) {
                return "<span class='label label-success'>已通过</span>";
            } elseif ($status == UserWalletWithdrawal::FAILD_STATUS) {
                return "<span class='label label-default'>未通过</span>";
            } 
        });

        $grid->filter(function($filter){

            // 去掉默认的id过滤器
            $filter->disableIdFilter();

            // 在这里添加字段过滤器
            $filter->equal('user_id', '用户ID');
            $filter->equal('phone', '手机号');

            // 在这里添加字段过滤器
            $filter->equal('status')->radio([
                ''   => '全部记录',
                0    => '申请中',
                1    => '已通过',
                2    => '未通过',
            ]);;
        });

        $grid->tools(function ($tools) {
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });
        });

        $grid->disableCreateButton();
        $grid->disableActions();

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    public function show($id, Content $content)
    {
        $record = UserWalletWithdrawal::findOrFail($id);
        $content->header('提币详情')
            ->breadcrumb(
                ['text' => '提币申请', 'url' => '/wallet/user-wallet-withdrawals'],
                ['text' => '提币详情']
            )
            ->body(Admin::show($record, function (Show $show) {

                $show->field('id', '提币订单号');
                $show->field('user_id', '提币用户ID');
                $show->field('created_at', '申请时间');
                $show->field('amount', '提币数量');
                $show->field('fee', '提币手续费');
                $show->field('to', '到账地址');
                $show->field('from', '钱包地址');
                $show->field('device_info', '设备型号');
       
                $show->panel()
                ->title('提币详情')
                ->tools(function ($tools) {
                    $tools->disableEdit();
                    $tools->disableList();
                    $tools->disableDelete();
                });
            }));
        
        if($record->status == UserWalletWithdrawal::PENDING_STATUS) {
            $content->row("<div class='container'>
                                <div class='row'>
                                    <div class='col-xs-12 col-md-2 col-md-offset-4'><a class='btn btn-warning' href='/admin/wallet/user-wallet-withdrawals/$id/decline'>拒绝</a></div>
                                    <div class='col-xs-12 col-md-2'><a class='btn btn-primary' href='/admin/wallet/user-wallet-withdrawals/$id/approve'>通过</a></div>
                                </div>
                            </div>"
            );
        }
        
        return $content;
    }

    public function edit($id, Content $content)
    {
        return Admin::content(function (Content $content) use ($id) {

            $content->header('提币详情')
            ->breadcrumb(
                ['text' => '提币申请', 'url' => '/wallet/user-wallet-withdrawals'],
                ['text' => '提币详情']
            )
            ->body($this->form()->edit($id));
        });

    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new UserWalletWithdrawal);

        $form->tools(function (Form\Tools $tools) {

            // 去掉`列表`按钮
            $tools->disableList();
        
            // 去掉`删除`按钮
            $tools->disableDelete();
        
            // 去掉`查看`按钮
            $tools->disableView();
        });

        $form->text('id','提币订单号')->readonly();
        
    
        $form->text('created_at','申请时间')->readonly();
        $form->text('fee','提币手续费')->readonly();
        
        $form->text('amount','提币数量')->readonly();
        $form->text('to', '到账地址')->readonly();
        $form->text('from', '钱包地址')->readonly();
        $form->text('device_info','设备信息')->readonly();

        $form->footer(function ($footer) {

            // 去掉`重置`按钮
            // $footer->disableReset();
        
            // 去掉`提交`按钮
            // $footer->disableSubmit();
        
            // 去掉`查看`checkbox
            $footer->disableViewCheck();
        
            // 去掉`继续编辑`checkbox
            $footer->disableEditingCheck();
        
            // 去掉`继续创建`checkbox
            $footer->disableCreatingCheck();
        });

        return $form;
    }

    public function getDecline($id)
    {
        $record = UserWalletWithdrawal::findOrFail($id);
        if($record->status !== UserWalletWithdrawal::PENDING_STATUS){
            return redirect("/admin/wallet/user-wallet-withdrawals/$id");
        }

        $record->status = UserWalletWithdrawal::FAILD_STATUS;

        $record->save();

        return redirect("/admin/wallet/user-wallet-withdrawals/$id");
    }

    public function getApprove($id)
    {
        $record = UserWalletWithdrawal::findOrFail($id);
        if($record->status !== UserWalletWithdrawal::PENDING_STATUS){
            return redirect("/admin/wallet/user-wallet-withdrawals/$id");
        }

        $record->status = UserWalletWithdrawal::COMPLETE_STATUS;

        $record->save();

        return redirect("/admin/wallet/user-wallet-withdrawals/$id");
    }
}
