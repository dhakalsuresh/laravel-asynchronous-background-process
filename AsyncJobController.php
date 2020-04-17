<?php

namespace App\Http\Controllers;

use App\Services\BackgroundCommand;

class AsyncJobController extends Controller
{
    protected $backgroundCommand;

    public function __construct(BackgroundCommand $backgroundCommand)
    {
        $this->backgroundCommand = $backgroundCommand;
    }
    
    /**
     * async process
     *
     * @return void
     */
    public function generateInvoice()
    {
        // php artisan cron:invoice
        $status = $this->backgroundCommand->init('cron:invoice')->run();
        
        return response()->json($status);
    }
}
