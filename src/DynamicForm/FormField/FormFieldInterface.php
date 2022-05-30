<?php

declare(strict_types=1);

namespace App\DynamicForm\FormField;

interface FormFieldInterface
{
    public function setFieldConfiguration(array $configuration);

    public function getFormFieldConfiguration(): array;
}
