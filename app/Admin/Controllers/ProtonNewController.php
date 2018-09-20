<?php

namespace App\Admin\Controllers;

use App\Models\ProtonNew;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class ProtonNewController extends Controller
{
    use ModelForm;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('Proton官网新闻列表');
            $content->description('Proton官网新闻后台');



            $content->body($this->grid());
        });
    }

    /**
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {

            $content->header('编辑新闻');
            $content->description('Proton官网新闻后台');

            $content->body($this->form()->edit($id));
        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {

            $content->header('创建新闻');
            $content->description('Proton官网新闻后台');

            $content->body($this->form());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(ProtonNew::class, function (Grid $grid) {
            $grid->model()->orderBy('status', 'desc')->orderBy('id', 'desc');
            $grid->id('ID')->sortable();
            $grid->column('title', '标题');
            $base_url = config('alioss.ossURL');
            $grid->column('img', '图片')->display(function ($img) use ($base_url) {
                return "<img src='$base_url/$img' class='kv-preview-data file-preview-image' style='width:auto;height:30px;''>";
            });
            $grid->column('status', '状态')->display(function ($status) {
                if ($status == ProtonNew::STASUS_NOMAL) {
                    return "<span class='label label-success'>显示</span>";
                }
                return "<span class='label label-danger'>不显示</span>";
            });
            $grid->column('type', '语言');
            $grid->column('url', '链接地址')->display(function ($url) {
                return "<a href='$url' target='_blank'>$url</a>";
            });
            $grid->column('created_at', '创建时间');
            $grid->updated_at('更新时间');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(ProtonNew::class, function (Form $form) {

            $form->display('id', 'ID');
            $form->text('title', '标题')->rules('required', [
                'required' => '标题必须填写',
            ]);
            $form->text('description', '描述');

            $form->hidden('img_base')->default(config('alioss.ossURL'));
            // 自定义文件名,路径
            $form->image('img', '图片')->name(function ($file) {
                return date('Y_m_d_H_i_s') . '_' . rand(1000, 9999) . '.' .$file->guessExtension();
            });

            $form->text('url', '链接')->rules('required', [
                'required' => '链接必须填写',
            ]);

            $language = [
                'zh'  => 'zh',
                'en' => 'en',
                'にほんご' => 'にほんご',
                '한국어' => '한국어'
            ];
            $form->select('type', '语言')->options($language);

            $status = ['不显示', '显示'];
            $form->select('status', '状态')->options($status);
        });
    }
}
