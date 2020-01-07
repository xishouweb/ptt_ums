<?php

namespace App\Admin\Controllers;

use App\Models\UserWalletWithdrawal;
use App\Models\UserWalletTransaction;
use App\Models\UserWalletBalance;
use App\Services\PttCloudAcount;
use App\Models\TransactionActionHistory;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Layout\Row;
use Illuminate\Support\Facades\DB;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Form as MyForm;
use Encore\Admin\Layout\Content;
use Encore\Admin\Facades\Admin;
use Illuminate\Support\Facades\Validator;

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
            return number_format($this->getBalanceOf('ptt'), 4);
        });

        $grid->column('amount', '提币数量')->display(function ($amount) {
            return number_format($amount, 4);
        });
        $grid->column('created_at', '提币时间');

        $grid->column('status', '状态')->display(function ($status) {
            if ($status === UserWalletWithdrawal::PENDING_STATUS) {
                return "<span class='label label-warning'>申请中</span>";
            } elseif ($status == UserWalletWithdrawal::COMPLETE_STATUS) {
                return "<span class='label label-success'>已通过</span>";
            } elseif ($status == UserWalletWithdrawal::FAILED_STATUS) {
                return "<span class='label label-default'>已拒绝</span>";
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
                2    => '已拒绝',
            ]);;
        });

        $grid->disableExport();
        $grid->disableColumnSelector();
        $grid->disableCreateButton();
        $grid->disableActions();
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
    public function show($id, Content $content)
    {
        $record = UserWalletWithdrawal::findOrFail($id);
        $statusStr = '';
        if ($record->status === 0) {
            $statusStr = "<h3><span class='label label-warning'>申请中</span></h3>";
        } elseif ($record->status === 1) {
            $statusStr = "<h3><span class='label label-success'>已通过</span></h3>";
        } elseif ($record->status === 2) {
            $statusStr = "<h3><span class='label label-default'>已拒绝</span></h3>";
        }

        $actionStr = '';
        if($record->status === UserWalletWithdrawal::PENDING_STATUS) {
            $actionStr = "<div class='col-xs-4'>
                            <div class='row'>
                                <div class='col-xs-3 col-xs-offset-3'>
                                    <h3><a class='btn btn-warning' href='/admin/wallet/user-wallet-withdrawals/$id/decline'>拒绝</a></h3>
                                </div>
                                <div class='col-xs-3'>
                                    <h3><a class='btn btn-success' href='/admin/wallet/user-wallet-withdrawals/$id/approve'>通过</a></h3>
                                </div>
                            </div>
                        </div>";
        }
        $content->header('提币详情')
            ->breadcrumb(
                ['text' => '提币申请', 'url' => '/wallet/user-wallet-withdrawals'],
                ['text' => '提币详情']
            )
            ->row("<div class='panel panel-default'>
                        <div class='container panel-body'>
                            <div class='row'>
                                <div class='col-xs-4'>
                                    <h3>提币订单号 : $record->id</h3>
                                </div>
                                <div class='col-xs-4'>
                                    $statusStr
                                </div>
                                $actionStr
                            </div>
                        </div>
                    </div>"
            )
            ->body(Admin::show($record, function (Show $show) use($record) {
                $show->field('user_id', '提币用户ID');
                $show->field('created_at', '申请时间');
                $show->amount('提币数量')->unescape()->as(function ($amount) {
                    return number_format($amount, 4);
                });
                $show->fee('提币手续费')->unescape()->as(function ($fee) {
                    return number_format($fee, 4);
                });
                $show->to('到账地址')->unescape()->as(function ($to) {
                    return "<a href='https://etherscan.io/address/$to' target='_blank'>$to</a>";
                });
                $show->field('device_info', '设备型号');

                if($record->status === UserWalletWithdrawal::COMPLETE_STATUS) {
                    $show->userWalletTransaction('Transaction Hash', function ($tx) {
                        $tx->tx_hash('Transaction Hash')->unescape()->as(function ($tx_hash) {
                            return "<a href='https://etherscan.io/tx/$tx_hash' target='_blank'>$tx_hash</a>";
                        });
                        $tx->from('发送地址')->unescape()->as(function ($from) {
                            return "<a href='https://etherscan.io/address/$from' target='_blank'>$from</a>";
                        });
                        $tx->updated_at('转账时间');
                        $tx->panel()
                        ->title('转账详情')
                        ->tools(function ($tools) {
                            $tools->disableEdit();
                            $tools->disableList();
                            $tools->disableDelete();
                        });
                    });
                }

                $show->panel()
                ->title('提币详情')
                ->tools(function ($tools) {
                    $tools->disableEdit();
                    $tools->disableList();
                    $tools->disableDelete();
                });
            }));

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

        $form->display('id', '提币订单号');
        $form->display('user_id', '提币用户ID');
        $form->display('created_at', '申请时间');
        $form->display('amount', '提币数量');
        $form->display('fee', '提币手续费');
        $form->display('to', '钱包地址')->with(function ($value) {
            return "<a href='https://etherscan.io/address/$value' target='_blank'>$value</a>";
        });
        $form->display('device_info', '设备型号')->readonly();

        $form->text('userWalletTransaction.tx_hash','Transaction Hash');

        $form->text('userWalletTransaction.from','发送地址');



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

        try {

            DB::beginTransaction();
            $record->status = UserWalletWithdrawal::FAILED_STATUS;

            $record->approver_id = Admin::user()->id;

            $record->save();

            $tx = UserWalletTransaction::findOrFail($record->user_wallet_transaction_id);
            $tx->status = UserWalletTransaction::OUT_STATUS_FAIL;

            $tx->save();

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
        }

        return redirect("/admin/wallet/user-wallet-withdrawals/$id");
    }

    public function getApprove($id)
    { 
        try {
            

            $record = UserWalletWithdrawal::findOrFail($id);
            if($record->status !== UserWalletWithdrawal::PENDING_STATUS){
                return redirect("/admin/wallet/user-wallet-withdrawals/$id");
            }
    
            $tx = UserWalletTransaction::findOrFail($record->user_wallet_transaction_id);
            if(!$tx){
                return redirect("/admin/wallet/user-wallet-withdrawals/$id");
            }
    
            $balance = UserWalletBalance::whereUserId($tx->user_id)->whereSymbol($tx->symbol)->first();
            $spending = $tx->fee + $tx->amount;
            if ($spending > $balance->locked_balance || $spending > $balance->total_balance) {
                throw new \Exception("余额不足, 请检查账户余额");
            }

            $block = PttCloudAcount::sendTransaction($tx->to, $spending * 1000000000000000000, 'ptt', [
                'from' => config('app.ptt_master_address'),
                'keystore' => config('app.ptt_master_address_keystore'),
                'password' => config('app.ptt_master_address_password'),
            ]);

            TransactionActionHistory::create([
                'user_id' => $tx->user_id,
                'symbol' => 'ptt',
                'amount' => $spending,
                'type' => 'send',
                'to' => $block['to'],
                'from' => $block['from'],
                'fee' => $block['gasUsed'] / 1000000000000000000,
                'tx_hash' => $block['transactionHash'],
                'block_number' => $block['blockNumber'],
                'payload' => json_encode($block)
            ]);

            DB::beginTransaction();
            
            $balance->locked_balance -= $spending;
            $balance->total_balance -= $spending;
            $balance->save();
            
            if(!$block['status']) throw new Exception("转账失败,请检查gas");
            

            $record->status = UserWalletWithdrawal::COMPLETE_STATUS;

            $record->approver_id = Admin::user()->id;
            $record->from = $block['from'];
            $record->save();

            $tx->status = UserWalletTransaction::OUT_STATUS_TRANSFER;
            $tx->tx_hash = $block['transactionHash'];
            $tx->from = $block['from'];
            $tx->save();

            

            DB::commit();

        } catch (\Exception $e) {
            admin_toastr('操作失败, 请检查提币账户余额或与管理员联系','error');
            \Log::error($e->getMessage());
            DB::rollBack();
        }

        return redirect("/admin/wallet/user-wallet-withdrawals/$id");
    }

        //todo
        //transaction hash      status 3  from addrss  对列
}
