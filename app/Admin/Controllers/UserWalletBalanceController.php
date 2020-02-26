<?php

namespace App\Admin\Controllers;

use App\Models\UserLoginInfo;
use App\Models\UserWalletBalance;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;
use Encore\Admin\Facades\Admin;
use App\Models\Tag;

class UserWalletBalanceController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '用户管理';

       /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('用户管理')
            ->breadcrumb(
                ['text' => '用户列表']
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
        $grid = new Grid(new UserWalletBalance);

        $grid->model()->orderBy('id', 'desc');

        $grid->column('user_id', '用户ID')->display(function ($user_id) {
            return "<a href='/admin/wallet/user-wallet-balances/$user_id' target='_blank'>$user_id</a>";
        });
        $grid->column('用户昵称')->display(function () {
            return $this->user['nickname'];
        });

        $grid->column('标签')->display(function() {
            $labels = $this->user->tags->pluck('name');
            $str = '';
            foreach ($labels as $key => $value) {
                $str .= "<span class='label label-success'>$value</span> ";
            }

            return "<a href='/admin/wallet/user-tags/$this->user_id' target='_blank'>$str</a>";
        });

        $grid->column('手机号')->display(function () {
            return $this->user['phone'];
        });

        $grid->total_balance('云端钱包余额(PTT)')->display(function ($total_balance) {
            return number_format($total_balance);
        });

        $grid->column('注册渠道')->display(function () {
            return $this->user['channel'];
        });

        $grid->column('注册时间')->display(function () {
            return date($this->user['created_at']);
        });

        $grid->column('交易记录')->display(function () {
            return "<a href='/admin/wallet/user-wallet-transactions/$this->user_id' target='_blank'>查看</a>";
        });

        $grid->column('操作记录')->display(function () {
            return "<a href='/admin/wallet/user-action-histories/$this->user_id' target='_blank'>查看</a>";
        });

        $grid->column('登录记录')->display(function () {
            return "<a href='/admin/wallet/user-login-infos/$this->user_id' target='_blank'>查看</a>";
        });


        $grid->filter(function($filter){

            // 去掉默认的id过滤器
            $filter->disableIdFilter();

            // 在这里添加字段过滤器
            $filter->like('users.nickname', '用户昵称');
            $filter->equal('user_id', '用户ID');
            $filter->equal('phone', '手机号');
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
        $user = UserWalletBalance::leftJoin('users', 'user_wallet_balances.user_id', '=', 'users.id')
            ->where('user_wallet_balances.user_id', $id)
            ->select('user_wallet_balances.*', 'nickname', 'avatar', 'phone', 'channel', 'users.created_at as register_time', 'last_login')
            ->first();
        $last_login = UserLoginInfo::where('user_id', $id)->orderBy('id', 'desc')->first();
        $user->last_login_time = '';
        if ($last_login) {
            $user->last_login_time = $last_login->created_at;
        }
        return $content->breadcrumb(
            ['text' => '用户列表', 'url' => '/wallet/user-wallet-balances'],
            ['text' => '用户详情']
        )
        ->body(Admin::show($user, function (Show $show) {
            $show->user_id('用户ID');
            $show->avatar('头像')->image(50, 50);
            $show->nickname('用户昵称');
            $show->address('钱包地址');
            $show->symbol('币种');
            $show->total_balance('钱包余额')->unescape()->as(function ($total_balance) {
                return number_format($total_balance, 4);
            });
            $show->locked_balance('冻结额度')->unescape()->as(function ($locked_balance) {
                return number_format($locked_balance, 4);
            });
            $show->phone('注册手机号');
            $show->register_time('注册时间');
            $show->channel('注册渠道');
            $show->last_login_time('上次登录时间');

            $show->tags('标签')->unescape()->as(function() {
                $labels = $this->user->tags->pluck('name');
                $str = '';
                foreach ($labels as $key => $value) {
                    $str .= "<span class='label label-success'>$value</span> ";
                }

                return "<a href='/admin/wallet/user-tags/$this->user_id' target='_blank'>$str</a>";
            });
            $show->note('备注');

            $show->panel()
                ->title('用户详情')
                ->tools(function ($tools) {
                    // $tools->disableEdit();
                    $tools->disableList();
                    $tools->disableDelete();
                });
        }));
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {


        $form = new Form(new UserWalletBalance);

        $form->text('note');

        $form->tools(function (Form\Tools $tools) {

            // 去掉`列表`按钮
            $tools->disableList();

            // 去掉`删除`按钮
            $tools->disableDelete();

            // 去掉`查看`按钮
            $tools->disableView();

            // // 添加一个按钮, 参数可以是字符串, 或者实现了Renderable或Htmlable接口的对象实例
            // $tools->add('<a class="btn btn-sm btn-danger"><i class="fa fa-trash"></i>&nbsp;&nbsp;delete</a>');
        });

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
