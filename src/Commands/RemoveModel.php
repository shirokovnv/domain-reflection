<?php


namespace Shirokovnv\DomainReflection\Commands;


use Illuminate\Console\Command;
use Shirokovnv\DomainReflection\Facades\DomainReflection;

/**
 * Class RemoveModel
 * @package Shirokovnv\DomainReflection\Commands
 */
class RemoveModel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'domain:remove {model_class_name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes information about specified model from database';

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
        $model = DomainReflection::removeReflection($this->argument('model_class_name'));
        if ($model) {
            echo "Model {$this->argument('model_class_name')} removed successfully";
        } else {
            echo "Couldn't remove model. ";
        }
    }
}
