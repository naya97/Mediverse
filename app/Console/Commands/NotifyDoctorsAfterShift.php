<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NotificationDoctorService;

class NotifyDoctorsAfterShift extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:notify-doctors-after-shift';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */

    protected $NotificationDoctorService;

    public function __construct(NotificationDoctorService $NotificationDoctorService)
    {
        parent::__construct();
        $this->NotificationDoctorService = $NotificationDoctorService;
    }

    public function handle()
    {
        $this->NotificationDoctorService->notifyDoctorsAfterShift();
        $this->info('Shift notifications sent successfully.');
    }
}
