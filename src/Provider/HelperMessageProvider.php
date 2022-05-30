<?php

declare(strict_types=1);

namespace App\Provider;

interface HelperMessageProvider
{
    public function getHelpMessage(string $key): string;
}
