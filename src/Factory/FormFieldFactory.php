<?php

declare(strict_types=1);

namespace App\Factory;

use App\DynamicForm\FormField\FormFieldInterface;

class FormFieldFactory
{
    /** @var array
     *
     */
    protected $fieldConfiguration;

    /** @var array */
    protected $formFieldConfiguration;

    /** @var FormFieldInterface */
    protected $formFields;

    public function addFormField($alias, FormFieldInterface $formField): void
    {
        $this->formFields[$alias] = $formField;
    }

    public function has($alias): bool
    {
        return isset($this->formFields[$alias]);
    }

    public function getFormField($alias): FormFieldInterface
    {
        if ($this->has($alias)) {
            return $this->formFields[$alias];
        }

        return $this->formFields['default'];
    }
}
