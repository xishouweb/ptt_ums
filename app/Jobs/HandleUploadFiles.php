<?php

namespace App\Jobs;

use App\Http\Controllers\Business\DataRecordController;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class HandleUploadFiles implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $vendor;
    protected $file_name;
    protected $user_application_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($vendor, $file_name, $user_application_id)
    {
        $this->vendor = $vendor;
        $this->file_name = $file_name;
        $this->user_application_id = $user_application_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $file_path = dirname(dirname(dirname(__FILE__))) . '/storage/app/' . $this->file_name;
        $flag = false;
        $fh = fopen($file_path, 'r');
        while (!feof($fh)) {
            $arr = explode(',', mb_convert_encoding(fgets($fh), "UTF-8"));
            if (count($arr) != 8) {
                continue;
            }
            $line = '{"phone": ' . $arr[0] . ',"gender": ' . $arr[1] . ',"age": ' . $arr[2] . ', "user_address": ' . $arr[3] . ', "industry": ' . $arr[4] . ', "hobby": ' . $arr[5] . ', "interest": ' . $arr[6] . ', "model": ' . $arr[7] . '}';
            if (is_null(json_decode($line))) {
                continue;
            }
            $res = DataRecordController::processingData($this->vendor, $this->user_application_id, $line, json_decode($line, true));
            if ($res['status'] == 200) {
                $flag = true;
            }
        }
        fclose($fh);
        if ($flag) {
            unlink($file_path);
            Log::info('上传成功，已删除文件：' . $this->file_name);
        } else {
            Log::info('上传失败，未删除文件：' . $this->file_name);
        }
    }
}
