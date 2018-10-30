<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProtonNew extends BaseModel implements FormatInterface
{
    use SoftDeletes;

    const STASUS_NOMAL = 1;

    const IS_TOP_YES = 1;

    protected $guarded = ['id'];

    public function format($source = [])
    {
        $data['title'] = $this->title;
        $data['description'] = $this->description;
        $data['img_base'] = $this->img_base;
        $data['img'] = $this->img;
        $data['url'] = $this->url;
        $data['release_date'] = $this->release;


        return $data;

    }
}
