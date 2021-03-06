<?php

declare(strict_types=1);

namespace App\Provider;

interface DataProvider
{
    public function getData(): array;
}
