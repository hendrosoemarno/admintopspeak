<?php

namespace App\Repositories;

use App\Models\AppConfiguration;

class AppConfigurationRepository extends Repository
{
    protected function model(): string
    {
        return AppConfiguration::class;
    }

    public function current(): AppConfiguration
    {
        return AppConfiguration::current();
    }
}