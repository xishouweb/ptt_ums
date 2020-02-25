<?php

namespace App\Admin\Extensions\Tools;

use Encore\Admin\Admin;
use Encore\Admin\Grid\Tools\AbstractTool;
use Illuminate\Support\Facades\Request;

class CustomButton extends AbstractTool
{
    protected $url;
    protected $icon;
    protected $text;
    protected $class;

    function __construct($url,$icon,$text, $class = 'btn-success')
    {
      $this->url = $url;
      $this->icon = $icon;
      $this->text = $text;
      $this->class = $class;
    }

    public function render()
    {
      $url = $this->url;
      $icon = $this->icon;
      $text = $this->text;
      $class = $this->class;
      return view('admin.tools.button', compact('url','icon','text', 'class'));
    }
}