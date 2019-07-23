<?php

namespace App\Admin\Controllers;

use App\Models\PriceQueryStatistic;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class PriceQueryStatisticController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('Index')
            ->description('description')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('Detail')
            ->description('description')
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('Edit')
            ->description('description')
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('Create')
            ->description('description')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new PriceQueryStatistic);
        $grid->id('Id');
        $grid->model()->with('user_xu_hosts.xu_nickname');
        $grid->column('用户昵称')->display(function () {
            return $this->user_xu_hosts['xu_nickname'];
        });
        // $grid->column('', '用户昵称');
        $grid->xu_host_id('Xu host id');
        $grid->xu_group_id('Xu group id');
        $grid->xu_robot_id('Xu robot id');
        $grid->symbol('Symbol');
        $grid->query_count('Query count');
        $grid->created_at('Created at');
        $grid->updated_at('Updated at');
        $grid->xu_group_name('Xu group name');
        $grid->column('status', '状态')->display(function ($status) {
            if ($status == 0) {
                return "<span class='label label-success'>正常</span>";
            }
            return "<span class='label label-danger'>违规</span>";
        });
        $grid->filter(function($filter){

            // 去掉默认的id过滤器
            $filter->disableIdFilter();

            // 在这里添加字段过滤器
            $filter->like('user_xu_hosts.xu_nickname', 'xu_nickname');
            $filter->like('xu_group_name', 'xu_group_name');
            $filter->equal('xu_group_id', 'xu_group_id');
            $filter->equal('symbol', 'symbol');
        });


        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableView();
        });


        $grid->disableRowSelector();
        $grid->disableCreateButton();
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
        $show = new Show(PriceQueryStatistic::findOrFail($id));

        $show->id('Id');
        $grid->column('用户昵称')->display(function () {
            return $this->user_xu_hosts['xu_nickname'];
        });
        $show->xu_host_id('Xu host id');
        $show->xu_group_id('Xu group id');
        $show->xu_robot_id('Xu robot id');
        $show->symbol('Symbol');
        $show->query_count('Query count');
        $show->created_at('Created at');
        $show->updated_at('Updated at');
        $show->xu_group_name('Xu group name');
        $show->status('Status');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new PriceQueryStatistic);

        // $form->model()->with('user_xu_hosts.xu_nickname');
        $form->text('user_xu_hosts.xu_nickname', '用户昵称')->disable();
        $form->text('xu_host_id', 'Xu host id')->disable();
        $form->text('xu_group_id', 'Xu group id')->disable();
        $form->text('xu_robot_id', 'Xu robot id')->disable();
        $form->text('symbol', 'Symbol')->disable();
        $form->text('query_count', 'Query count')->disable();
        $form->text('xu_group_name', 'Xu group name')->disable();
        $form->switch('status', 'Status');

        return $form;
    }
}
