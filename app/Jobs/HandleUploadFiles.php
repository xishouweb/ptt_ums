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
            $line = fgets($fh);
            if (!is_null(json_decode($line))) {
                DataRecordController::processingData($this->vendor, $this->user_application_id, $line, json_decode($line, true));
                $flag = true;
            }
        }
        fclose($fh);
        if ($flag) {
            unlink($file_path);
            Log::info('上传成功，已删除文件：' . $this->file_name);
        }
    }
}
