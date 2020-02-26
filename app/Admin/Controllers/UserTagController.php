<?php

namespace App\Admin\Controllers;

use App\Models\UserTag;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;
use Encore\Admin\Facades\Admin;
use App\Admin\Actions\Post\Restore;
use App\Models\Tag;
use App\Admin\Extensions\Tools\CustomButton;

class UserTagController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '用户标签管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid($user_id)
    {
        $grid = new Grid(new UserTag);

        $grid->model()->whereUserId($user_id)->orderBy('id', 'desc');
        $grid->column('user_id', __('User id'));
        $grid->tag('标签')->display(function($tag) {
            return "<span class='label label-success'>" . $tag['name'] ."</span>";
        });
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

        $grid->filter(function ($filter) {

            // 范围过滤器，调用模型的`onlyTrashed`方法，查询出被软删除的数据。
            $filter->scope('trashed', '回收站')->onlyTrashed();
             // 去掉默认的id过滤器
            $filter->disableIdFilter();
        });

        $grid->disableExport();
        $grid->disableColumnSelector();
        $grid->disableCreateButton();
        $grid->actions(function ($actions) {
            // 去掉编辑
            $actions->disableEdit();

            // 去掉查看
            $actions->disableView();
            if (\request('_scope_') == 'trashed') {
                $actions->add(new Restore());
            }
        });

        $grid->tools(function ($tools) {
            $url = "/admin/wallet/user-tags/create";
            $icon = "fa-plus";
            $text = "标记用户";
            $class = 'btn-danger';
            $tools->append(new CustomButton($url,$icon,$text, $class));

            $url = "/admin/wallet/tags/create";
            $icon = "fa-plus";
            $text = "创建标签";
            $tools->append(new CustomButton($url,$icon,$text));
        });
        // 去掉批量操作
        $grid->disableBatchActions();
        // $grid->batchActions(function ($batch) {
        //     $batch->disableDelete();
        // });

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
        return $content
        ->header('标签管理')
        ->breadcrumb(
            ['text' => '用户列表', 'url' => '/wallet/user-wallet-balances'],
            ['text' => '标签管理']
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
        $show = new Show(UserTag::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('user_id', __('User id'));
        $show->field('tag_id', __('Tag id'));
        $show->field('deleted_at', __('Deleted at'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    public function form()
    {
        $form = new Form(new UserTag);

        $form->text('user_id', 'User id');
        $form->select('tag_id', '标签')->options(Tag::all()->pluck('name', 'id'));
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

    public function delete($user_id, $id)
    {
       $record = UserTag::find($id);
       $record->delete();

       return response()->json([
                'status'  => true,
                'message' => trans('admin::lang.delete_succeeded'),
            ]);
    }
}
