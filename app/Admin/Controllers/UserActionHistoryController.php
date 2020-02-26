<?php

namespace App\Admin\Controllers;

use App\Models\UserActionHistory;
use App\Models\UserWalletBalance;
use App\Models\UserWalletTransaction;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Widgets\Tab;

class UserActionHistoryController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '操作记录';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid($user_id)
    {
        $grid = new Grid(new UserActionHistory);

        $grid->model()->whereUserId($user_id)->orderBy('id', 'desc');

        $grid->column('id', 'ID');
        $grid->column('type', '操作类型')->display(function($type){
            if ($type === 1) {
                return '转入';
            } elseif ($type === 2) {
                return "提币";
            } elseif ($type === 3) {
                return "持仓报名";
            } elseif ($type === 4) {
                return "取消持仓";
            }
        });

        $grid->column('状态')->display(function(){

            if ($this->type === 1) {
                if ($this->userWalletTransaction->status === UserWalletTransaction::IN_STATUS_PADDING) {
                    return '区块确认中';
                } elseif ($this->userWalletTransaction->status === UserWalletTransaction::IN_STATUS_SUCCESS) {
                    return '已完成';
                }
            } elseif ($this->type === 2) {
                if ($this->userWalletTransaction->status === UserWalletTransaction::OUT_STATUS_PADDING) {
                    return '申请中';
                } elseif ($this->userWalletTransaction->status === UserWalletTransaction::OUT_STATUS_SUCCESS) {
                    return '已完成';
                } if ($this->userWalletTransaction->status === UserWalletTransaction::OUT_STATUS_TRANSFER) {
                    return '转账中';
                } elseif ($this->userWalletTransaction->status === UserWalletTransaction::OUT_STATUS_FAIL) {
                    return '已拒绝';
                }
            } elseif ($this->type === 3) {
                return "-";
            } elseif ($this->type === 4) {
                return "-";
            }
        });

        $grid->column('数量(PTT)')->display(function() {
            if ($this->type === 1) {
                return number_format($this->userWalletTransaction->amount);
            } elseif ($this->type === 2) {
                return number_format($this->userWalletTransaction->amount);
            } elseif ($this->type === 3) {
                return "-";
            } elseif ($this->type === 4) {
                return "-";
            }
        });

        $grid->column('手续费(PTT)')->display(function() {
            if ($this->type === 1) {
                return number_format($this->userWalletTransaction->fee);
            } elseif ($this->type === 2) {
                return number_format($this->userWalletTransaction->fee);
            } elseif ($this->type === 3) {
                return "-";
            } elseif ($this->type === 4) {
                return "-";
            }
        });

        $grid->column('时间)')->display(function() {
            if ($this->type === 1) {
                $created_at =$this->userWalletTransaction->created_at;
                $completed_at = $this->userWalletTransaction->completed_at;
                return "开始时间: $created_at </br> 结束时间: $completed_at";
            } elseif ($this->type === 2) {
                $created_at =$this->userWalletTransaction->created_at;
                $completed_at = $this->userWalletTransaction->completed_at;
                return "开始时间: $created_at </br> 结束时间: $completed_at";
            } elseif ($this->type === 3) {
                return "开始时间: $this->created_at </br> 结束时间: -";
            } elseif ($this->type === 4) {
                return "开始时间: - </br> 结束时间: $this->created_at";
            }
        });

        $grid->column('balance', '钱包余额(PTT)')->display(function($balance) {
            return number_format($balance);
        });

        $grid->column('持仓活动名称')->display(function($balance) {
            if ($this->saving_id) {
                $saving_title = $this->savings->title;
                return "<a href='/admin/wallet/savings/$this->saving_id' target='_blank'>$saving_title</a>";
            }

            return '未参加';
        });

        $grid->column('地址')->display(function($balance) {
            if ($this->type === 1) {
                $to = $this->userWalletTransaction->to;
                return "To: <a href='https://etherscan.io/address/$to' target='_blank'>$to</a>";
            } elseif ($this->type === 2) {
                $to = $this->userWalletTransaction->to;
                return "To: <a href='https://etherscan.io/address/$to' target='_blank'>$to</a>";
            } elseif ($this->type === 3) {
                return "-";
            } elseif ($this->type === 4) {
                return "-";
            }
        });

        $grid->filter(function($filter){

            // 去掉默认的id过滤器
            $filter->disableIdFilter();

            // 在这里添加字段过滤器
            $filter->equal('type')->radio([
                ''   => '全部记录',
                1    => '转入记录',
                2    => '提币记录',
                3    => '报名持仓',
                4    => '取消持仓',
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

    public function show($user_id, Content $content)
    {
        $user = UserWalletBalance::leftJoin('users', 'user_wallet_balances.user_id', '=', 'users.id')
            ->where('user_wallet_balances.user_id', $user_id)
            ->select('user_wallet_balances.*', 'nickname', 'avatar', 'phone')
            ->first();

        return $content
        ->header('操作记录')
        ->breadcrumb(
            ['text' => '用户列表', 'url' => '/wallet/user-wallet-balances'],
            ['text' => '操作记录']
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
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(UserActionHistory::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('type', __('Type'));
        $show->field('user_id', __('User id'));
        $show->field('transaction_id', __('Transaction id'));
        $show->field('saving_id', __('Saving id'));
        $show->field('withdrawal_id', __('Withdrawal id'));
        $show->field('balance', __('Balance'));
        $show->field('remark', __('Remark'));
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
        $form = new Form(new UserActionHistory);

        $form->number('type', __('Type'));
        $form->number('user_id', __('User id'));
        $form->number('transaction_id', __('Transaction id'));
        $form->number('saving_id', __('Saving id'));
        $form->number('withdrawal_id', __('Withdrawal id'));
        $form->decimal('balance', __('Balance'));
        $form->text('remark', __('Remark'));

        return $form;
    }
}
