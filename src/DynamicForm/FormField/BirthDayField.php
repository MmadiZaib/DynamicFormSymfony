<?php

declare(strict_types=1);

namespace App\DynamicForm\FormField;


class BirthDayField extends FormField
{
    protected function getFieldType(): string
    {
        return 'birthday';
    }
}
