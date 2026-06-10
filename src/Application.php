<?php

namespace KodaInit;

use Symfony\Component\Console\Application as SymfonyApplication;
use KodaInit\Commands\InitCommand;

class Application extends SymfonyApplication
{
    public function __construct()
    {
        parent::__construct(
            'Koda Init',
            '0.1.0'
        );

        $this->add(new InitCommand());
    }
}