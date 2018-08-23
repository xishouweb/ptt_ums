<?php

namespace App\Admin\Controllers;

use App\Models\Banner;
use App\Models\Photo;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BannerController extends Controller
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
            $content->header('公告列表');
            $content->body($this->grid());
        });
    }

    /**
     * Edit interface.
     *
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {
            $content->header('header');
            $content->description('description');
            $content->body($this->form()->edit($id));
        });
    }

    /**
     * Update interface.
     *
     * @return Content
     */
    public function update($id, Request $request)
    {
        //验证表单
        $validator = Validator::make($request->all(), [
            'title'   => 'required',
            'content' => 'required',
            'type'    => 'required',
            'sort'    => 'required',
        ], [
            'required' => ':attribute必须填写',
        ]);
        if ($validator->fails()) {
            return redirect("admin/wallet/banner/$id/edit")
                ->withErrors($validator)
                ->withInput();
        }

        //更新操作
        $banner = Banner::find($id);
        $banner->title = $request->input('title');
        $banner->content = $request->input('content');
        $banner->type = $request->input('type');
        $banner->sort = $request->input('sort');
        //判断更新图片与否
        if ($request->hasFile('image')) {
            $photo = Photo::upload($request, 'image', 'app/banner');
            if ($photo) {
                $banner->image = $photo->url;
            }
        }
        $banner->save();

        return redirect('admin/wallet/banner');
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create(Request $request)
    {
        //验证表单
        $validator = Validator::make($request->all(), [
            'title'   => 'required',
            'image'   => 'required',
            'content' => 'required',
            'type'    => 'required',
            'sort'    => 'required',
        ], [
            'required' => ':attribute必须填写',
        ]);
        if ($validator->fails()) {
            return redirect('admin/wallet/banner/create')
                ->withErrors($validator)
                ->withInput();
        }

        //上传图片
        $photo = Photo::upload($request, 'image', 'app/banner');
        if (!$photo) {
            return $this->apiResponse([], '图片上传失败!', 1);
        }

        //创建记录
        Banner::create([
            'title'   => $request->input('title'),
            'image'   => $photo->url,
            'content' => $request->input('content'),
            'type'    => $request->input('type'),
            'sort'    => $request->input('sort'),
            'status'  => Banner::ENABLED,
        ]);

        return redirect('admin/wallet/banner');
    }

    public function createForm()
    {
        return Admin::content(function (Content $content) {
            $content->header('创建公告');
            $content->body($this->form());
        });
    }

    /**
     * Delete interface.
     *
     * @return Content
     */
    public function delete($id)
    {
        $Banner = Banner::find($id);
        if ($Banner->status) {
            $Banner->status = Banner::NOT_ENABLED;
        } else {
            $Banner->status = Banner::ENABLED;
        }
        $Banner->save();
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(Banner::class, function (Grid $grid) {
            $grid->model()->orderBy('status', 'desc')->orderBy('id', 'desc');
            $grid->id('ID')->sortable();
            $grid->column('title', '标题');
            $grid->column('image', '图片')->display(function ($image) {
                return "<img width='150px' src='$image'>";
            });
            $grid->column('status', '状态')->display(function ($status) {
                if ($status == Banner::ENABLED) {
                    return "<span class='label label-success'>启用</span>";
                }
                return "<span class='label label-danger'>未启用</span>";
            });
            $grid->column('type', '类型')->display(function ($type) {
                if ($type == Banner::TYPE_WEB) {
                    $text = Banner::TYPE_TEXT[Banner::TYPE_WEB];
                } else {
                    $text = Banner::TYPE_TEXT[Banner::TYPE_APP];
                }
                return "<span class='label label-warning'>$text</span>";
            });
            $grid->column('content', '内容');
            $grid->column('sort', '排序');
            $grid->column('created_at', '创建时间');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(Banner::class, function (Form $form) {
            $form->display('id', 'ID');
            //todo rules不起作用
            $form->text('title', '标题')->rules('required', [
                'required' => '标题必须填写',
            ]);
            $form->image('image', '图片');
            $form->text('content', '内容（如：链接）')->rules('required', [
                'required' => '内容必须填写',
            ]);
            $form->radio('type', '类型')->options(Banner::TYPE_TEXT)->rules('required', [
                'required' => '类型必须填写',
            ]);
            $form->text('sort', '排序（数字越小，排位越前）')->default(1)->rules('required', [
                'required' => '排序必须填写',
            ]);
            $form->display('created_at', '创建时间');
            $form->display('updated_at', '修改时间');
        });
    }
}
