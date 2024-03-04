<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Helpers\Common;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class RunMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:migration {db?} {file?} {--ps}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run migrations for all/specific db';

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
        $db_name = $this->argument('db');
        $file    = $this->argument('file');
        $ps = $this->option('ps');
        if (!$db_name) {
            $clients = Client::whereNull('deleted_at')
                               ->get();
            if ($clients && count($clients) > 0) {
                foreach($clients as $key => $client) {
                    $database = config('database.connections.client.db_prefix').$client->unique_id;
                    DB::statement("CREATE DATABASE IF NOT EXISTS $database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
                    Common::changeClient($client->unique_id);
                    echo "Selected database ".$database."\n";
                    Artisan::call('migrate');
                    $output = Artisan::output();
                    echo $output;
                }
            } else {
                echo "No Clients to migrate";exit;
            }
        } else {
            if ($db_name == 'external') {
                $database = config('database.connections.mysql.external_database');
                DB::statement("CREATE DATABASE IF NOT EXISTS $database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
                Common::changeClient(null, true);
                echo "Selected database ".$database."\n";
                Artisan::call('migrate', ['--path' => 'database/migrations/external/'.$file]);
                $output = Artisan::output();
                echo $output;
            } else {
                $client = Client::whereNull('deleted_at')->where('id', '=', $db_name)->first();
                if ($client) {
                    $db_name = $client->unique_id;
                    $database = config('database.connections.client.db_prefix').$db_name;
                    DB::statement("CREATE DATABASE IF NOT EXISTS $database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
                    Common::changeClient($db_name);
                    echo "Selected database ".$database."\n";
                    if ($ps) {
                        Artisan::call('migrate', ['--path' => 'database/migrations/'.$client->id.'/']);
                    } else {
                        Artisan::call('migrate');
                    }
                    $output = Artisan::output();
                    echo $output;
                } else {
                    echo "No client found.";
                }
            }
        }
        Common::changeClient();
    }
}
