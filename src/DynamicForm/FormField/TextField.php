<?php


namespace App\DynamicForm\FormField;


class TextField extends FormField
{
    protected function getFieldType(): string
    {
        return 'text';
    }
}
