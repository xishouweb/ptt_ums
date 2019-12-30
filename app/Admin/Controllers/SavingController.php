<?php

namespace App\Admin\Controllers;

use App\Models\Saving;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;

class SavingController extends AdminController
{
    
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'App\Models\Saving';

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index(Content $content)
    {
        return $content->header('活动管理')
                ->breadcrumb(
                    ['text' => '活动列表']
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
        $grid = new Grid(new Saving);

        $grid->model()->orderBy('status')->orderBy('id', 'desc');

        $grid->column('icon', '图片')->image('', 60,60);
        $grid->id('活动编号');
        $grid->column('status', '状态')->display(function ($status) {
            if ($status === 0) {
                return "<span class='label label-warning'>待审批</span>";
            } elseif ($status === 1) {
                return "<span class='label label-success'>进行中</span>";
            } elseif ($status === 2) {
                return "<span class='label label-primary'>已通过</span>";
            } elseif ($status === 3) {
                return "<span class='label label-info'>已结束</span>";
            } elseif ($status === 4) {
                return "<span class='label label-default'>已驳回</span>";
            }
           
        });
        
        $grid->column('created_at', '创建时间');

        $grid->column('type', '类型')->display(function ($type) {
            if ($type == 1) {
                $text = "PoS 持仓活动";
            } elseif ($type == 2) {
                $text = "提币";
            } elseif ($type == 3) {
                $text = "收益";
            }
            return $text;
        });

        $grid->column('')->display(function () {
            return "<span class='label label-default'><a href='/admin/wallet/savings/$this->id' target='_blank'>详情</a></span>";
        });

        $grid->filter(function($filter){

            // 去掉默认的id过滤器
            $filter->disableIdFilter();

            // 在这里添加字段过滤器
            $filter->like('users.nickname', '用户昵称');
            $filter->equal('user_id', '用户ID');
            $filter->equal('phone', '手机号');
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
    public function show($id, Content $content)
    {
        $saving = Saving::findOrFail($id);
        $status = '';
        if ($saving->status === 0) {
            $status = "<h3><span class='label label-warning'>待审批</span></h3>";
        } elseif ($status === 1) {
            $status = "<h3><span class='label label-success'>进行中</span></h3>";
        } elseif ($status === 2) {
            $status = "<h3><span class='label label-primary'>已通过</span></h3>";
        } elseif ($status === 3) {
            $status = "<h3><span class='label label-info'>已结束</span></h3>";
        } elseif ($status === 4) {
            $status = "<h3><span class='label label-default'>已驳回</span></h3>";
        }

        if ($saving->type === 1) {
            $text = 'PoS 持仓活动';
        }

        $creater = $saving->users->name;
        $actionStr = '';
   
        if(Admin::user()->inRoles(['administrator'])) {
            $actionStr = "<div class='row'>
                            <div class='col-xs-12 col-md-2 col-md-offset-2'><a class='btn btn-warning' href='/admin/wallet/savings/$id/decline'>驳回</a></div>
                            <div class='col-xs-12 col-md-2 col-md-offset-3'><a class='btn btn-success' href='/admin/wallet/savings/$id/approve'>通过</a></div>
                        </div>";
        }
        
        $content->header('活动详情')
            ->breadcrumb(
                ['text' => '活动列表', 'url' => '/wallet/savings'],
                ['text' => '活动详情']
            )
            ->row("<div class='panel panel-default'>
                        <div class='container panel-body'>
                            <div class='row'>
                                <div class='col-xs-2'>
                                    <img src='$saving->icon' class='img-rounded img-thumbnail' style='width: 50%'>
                                </div>
                                <div class='col-xs-10'>
                                    <div class='row'>
                                        <div class='col-xs-6'>
                                            <h3>活动编号: $saving->id</h3>
                                        </div>
                                        <div class='col-xs-6'>
                                            $status
                                        </div>
                                    </div>
                                    <div class='row'>
                                        <div class='col-xs-4'>
                                            <h4>创建人: $creater </h4>
                                        </div>
                                        <div class='col-xs-4'>
                                            <h4>创建时间: $saving->created_at</h4>
                                        </div>
                                        <div class='col-xs-4'>
                                            <h4>类型：$text</h4>
                                        </div>
                                    </div>
                                    $actionStr
                                </div>
                            </div>
                        </div>
                    </div>"
            )
            ->body(Admin::show($saving, function (Show $show) {
                $show->title('活动名称');
                $show->title_en('Activity name');
                $show->column('始终日期')->as(function() {
                    return $this->started_at . ' ~ ' . $this->ended_at;
                });
                $show->entry_standard('单个账号持仓最小值')->as(function ($entry_standard) {
                    return number_format($entry_standard);
                });
                $show->rate('持仓年化收益率')->as(function ($rate) {
                    return $rate * 100 . '%';
                });
                $show->detail_rule('规则说明')->unescape()->as(function ($detail_rule) {
                    return $detail_rule;
                });
                $show->detail_rule_en('Rules')->unescape()->as(function ($detail_rule_en) {
                    return $detail_rule_en;
                });
            
                $show->panel()
                    ->title('活动详情')
                    ->tools(function ($tools) {
                        $tools->disableEdit();
                        $tools->disableList();
                        $tools->disableDelete();
                    });
            }));

        

        return $content;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Saving);
        $form->tools(function (Form\Tools $tools) {

            // 去掉`列表`按钮
            $tools->disableList();
        
            // 去掉`删除`按钮
            $tools->disableDelete();
        
            // 去掉`查看`按钮
            $tools->disableView();
        });

        $form->footer(function ($footer) {
        
            // 去掉`查看`checkbox
            $footer->disableViewCheck();
        
            // 去掉`继续编辑`checkbox
            $footer->disableEditingCheck();
        
            // 去掉`继续创建`checkbox
            $footer->disableCreatingCheck();
        
        });

        $form->display('id', 'ID');
        $form->text('title','活动名称');
        $form->text('title_en', 'Activity name');
        $form->image('icon', '图片');
        $form->select('type', '类型')->options([1 => 'PoS 持仓活动', 2 => 'bar']);
        $form->select('yield_time', '奖励发放方式')->options([1 => 'afsdadf', 2 => 'basdfasdfar']);
        $form->hidden('status')->default(0);
        $form->datetime('started_at','开始日期');
        $form->datetime('ended_at','结束日期');
        $form->number('entry_standard', '单个账号持仓最小值');
    
        $form->number('rate','持仓年化收益率');
        $form->textarea('detail_rule', '规则说明');
        $form->textarea('detail_rule_en', 'Rules');

        $form->hidden('user_id');
        $form->saving(function (Form $form) {
            $form->user_id = Admin::user()->id;
        });


        return $form;
    }
}
