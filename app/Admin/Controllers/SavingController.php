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
                return "<h4><span class='label label-info'>已结束</span></h4>";
            } elseif ($status === 1) {
                return "<h4><span class='label label-success'>进行中</span></h4>";
            } elseif ($status === 2) {
                return "<h4><span class='label label-primary'>已通过</span></h4>";
            } elseif ($status === 3) {
                return "<h4><span class='label label-warning'>待审批</span></h4>";
            } elseif ($status === 4) {
                return "<h4><span class='label label-default'>已驳回</span></h4>";
            }
           
        });
        
        $grid->column('created_at', '创建时间');

        $grid->column('type', '类型')->display(function ($type) {
            if ($type == 1) {
                $text = "PoS 持仓活动";
            } 
            return $text;
        });
        
        $grid->column('操作')->display(function () {
            
            if ($this->status === 2) {
                return "<a class='btn btn-success' href='/admin/wallet/savings/$this->id/online'>上线</a>";
            }elseif ($this->status === 1) {
                return "<a class='btn btn-info' href='/admin/wallet/savings/$this->id/offline'>结束</a>";
            }else{
                return '';
            }

        });

        $grid->column('')->display(function () {
            return "<span class='label label-default'><a href='/admin/wallet/savings/$this->id' target='_blank'>详情</a></span>";
        });

        $grid->filter(function($filter){

            // 去掉默认的id过滤器
            $filter->disableIdFilter();

            // 在这里添加字段过滤器
            $filter->like('title', '活动名称');
            $filter->equal('id', '活动编号');
            $filter->equal('type')->radio([
                0   => '已结束',
                1    => '进行中',
                2    => '已通过',
                3    => '待审核',
                4    => '已驳回',
            ]);
        });

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
    public function show($id, Content $content)
    {
        $saving = Saving::findOrFail($id);
        $status = '';
        if ($saving->status === 0) {
            $status = "<h3><span class='label label-info'>已结束</span></h3>";
        } elseif ($saving->status === 1) {
            $status = "<h3><span class='label label-success'>进行中</span></h3>";
        } elseif ($saving->status === 2) {
            $status = "<h3><span class='label label-primary'>已通过</span></h3>";
        } elseif ($saving->status === 3) {
            $status = "<h3><span class='label label-warning'>待审批</span></h3>";
        } elseif ($saving->status === 4) {
            $status = "<h3><span class='label label-default'>已驳回</span></h3>";
        }

        if ($saving->type === 1) {
            $text = 'PoS 持仓活动';
        }

        $creater = $saving->users->name;
        $actionStr = '';
   
        if(Admin::user()->inRoles(['administrator']) && $saving->status == Saving::SAVING_DEFAULT_AUDIT_STATUS) {
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
                    return number_format($entry_standard, 4);
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
        $form->select('type', '类型')->options([1 => 'PoS 持仓活动']);
        $form->select('yield_time', '奖励发放方式')->options([1 => '每日发放']);
        $form->hidden('status')->default(Saving::SAVING_DEFAULT_AUDIT_STATUS);
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

    public function getDecline($id)
    {
        if(!Admin::user()->inRoles(['administrator'])){
            return redirect("/admin/wallet/savings/$id");
        }
        $saving = Saving::findOrFail($id);
        
        $saving->status = Saving::SAVING_APPLY_FAILED_STATUS;
        $saving->approver_id = Admin::user()->id;
        $saving->save();

        return redirect("/admin/wallet/savings/$id");
    }

    public function getApprove($id)
    {
        if(!Admin::user()->inRoles(['administrator'])){
            return redirect("/admin/wallet/savings/$id");
        }
        $saving = Saving::findOrFail($id);
        
        $saving->status = Saving::SAVING_APPLY_SUCCESS_STATUS;
        $saving->approver_id = Admin::user()->id;
        $saving->save();

        return redirect("/admin/wallet/savings/$id");
    }

    public function getOnline($id)
    {
        $saving = Saving::find($id);
        $saving->status = Saving::SAVING_ACTIVATED_STATUS;
        $saving->save();

        return  redirect()->back();
    }

    public function getOffline($id)
    {
        $saving = Saving::find($id);
        $saving->status = Saving::SAVING_UNACTIVATED_STATUS;
        $saving->save();

        return  redirect()->back();
    }
}
