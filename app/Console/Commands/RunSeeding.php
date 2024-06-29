<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Helpers\Common;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class RunSeeding extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:seeding {db?} {--class=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run seeders for specific db/all';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $db_name    = $this->argument('db');
        $class      = $this->option('class');
        if (!$db_name) {
            $clients = Client::whereNull('deleted_at')
                               ->get();
            if ($clients && count($clients) > 0) {
                foreach($clients as $key => $client) {
                    Common::changeClient($client->unique_id);
                    echo $class;
                    if ($class) {
                        Artisan::call('db:seed', ['--class' => $class]);
                    } else {
                        Artisan::call('db:seed');
                    }
                    $output = Artisan::output();
                    echo $output;
                }
            } else {
                echo "No Clients to seed";exit;
            }
        } else {
            $client = Client::whereNull('deleted_at')->where('id', '=', $db_name)->first();
            if ($client) {
                Common::changeClient($client->unique_id);
                if ($class) {
                    Artisan::call('db:seed', ['--class' => $class]);
                } else {
                    Artisan::call('db:seed');
                }
                $output = Artisan::output();
                echo $output;
            } else {
                echo "No client found.";
            }
        }
        Common::changeClient();
    }
}
