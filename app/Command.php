<?php

namespace App;

use App\Repositories\ConfigRepository;
use App\Repositories\ComposeRepository;
use App\Repositories\ServicesRepository;
use LaravelZero\Framework\Commands\Command as BaseCommand;

abstract class Command extends BaseCommand
{
    /**
     * The configuration repository.
     *
     * @var ConfigRepository
     */
    protected $config;

    /**
     * The configuration repository.
     *
     * @var ComposeRepository
     */
    protected $compose;

    /**
     * The configuration repository.
     *
     * @var ServicesRepository
     */
    protected $services;

    /**
     * Creates a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->config = resolve(ConfigRepository::class);
        $this->compose = resolve(ComposeRepository::class);
        $this->services = resolve(ServicesRepository::class);
    }
}
