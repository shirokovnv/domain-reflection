<?php


namespace Shirokovnv\DomainReflection\Commands;


use Illuminate\Console\Command;
use Shirokovnv\DomainReflection\Facades\DomainReflection;

/**
 * Class InitDomain
 * @package Shirokovnv\DomainReflection\Commands
 */
class InitDomain extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'domain:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reflect meta information about domain models to database';

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
        DomainReflection::registerDomainModels();

        echo "domain initialized successfully.";
    }
}
