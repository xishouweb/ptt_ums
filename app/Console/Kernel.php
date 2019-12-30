<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\AnchorUploadData::class,
        Commands\AnalysisItem::class,
        Commands\PttMonitorTrading::class,
        Commands\PttMonitorTradingTxHash::class,
        Commands\CheckUserSavingStatus::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('ptt:monitor_trading')->everyMinute();
        $schedule->command('ptt:monitor_trading_tx_hash')->everyMinute();
        $schedule->command('ptt:check_user_saving_status')->hourlyAt(55);
        $schedule->command('ptt:saving_issue_reward')->dailyAt('00:05');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
