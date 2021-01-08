<?php


namespace Shirokovnv\DomainReflection\Commands;


use Illuminate\Console\Command;
use Shirokovnv\DomainReflection\Facades\DomainReflection;

/**
 * Class ReloadModel
 * @package Shirokovnv\DomainReflection\Commands
 */
class ReloadModel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'domain:reload {model_class_name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reloads information about specified model to database';

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
     * @return mixed
     */
    public function handle()
    {
        $model = DomainReflection::reflectModelToDB($this->argument('model_class_name'));
        if ($model) {
            echo "Model {$this->argument('model_class_name')} registered successfully";
        } else {
            echo "Couldn't register model. ";
        }
    }
}
