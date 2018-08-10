<?php

namespace App\Models;

use App\Services\OSS;
use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    public static function upload($request, $key='photo', $sub_path = 'ptt/campaign')
    {
        if(!$request->hasFile($key)){
            return false;
        }
        $file = $request->file($key);

        $extension = "png";

        if(auth()->user()){
            $user_id = auth()->user()->id;
        }else{
            $user_id = 0;
        }
        $fileName  =  date('Y_m_d_H_i_s') . '_' . $user_id . '_' . rand(1000, 9999) .'.'.$extension;
        OSS::upload($sub_path . $fileName, $file);

        $photo = new Photo();
        $photo->extension = $extension;
        $photo->name      = $fileName;
        $photo->url       = config('alioss.ossURL') . '/' . $sub_path . '/' . $fileName;
        $photo->path      = $sub_path . '/' . $fileName;
        $photo->size      = $file->getClientSize();
        $photo->file      = '';

        $photo->save();
        return $photo;
    }
}
