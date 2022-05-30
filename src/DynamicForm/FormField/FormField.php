<?php

declare(strict_types=1);

namespace App\DynamicForm\FormField;

abstract class FormField implements FormFieldInterface
{
    /** @var array */
    protected $fieldConfiguration;

    /** @var array */
    protected $formFieldConfiguration;

    public function setFieldConfiguration(array $fieldConfiguration): void
    {
        $this->fieldConfiguration = $fieldConfiguration;
    }

    public function getFormFieldConfiguration(): array
    {
        $this->buildConfiguration();
    }

    protected function buildConfiguration()
    {
        $this->formFieldConfiguration = [];
        $this->formFieldConfiguration['key'] = $this->fieldConfiguration['name'];
        $this->formFieldConfiguration['type'] = 'input';

        if (isset($this->fieldConfiguration['options']['data'])) {
            $this->formFieldConfiguration['defaultValue'] = $this->fieldConfiguration['options']['data'];
            unset($this->formFieldConfiguration['options']['data']);
        }

        if (isset($this->fieldConfiguration['options'])) {
            $templateOptions = $this->fieldConfiguration['options'];
            $templateOptions['label'] = ucfirst($this->fieldConfiguration['name']);

            if (isset($this->fieldConfiguration['options']['label'])) {
                $templateOptions['label'] = ucfirst($this->fieldConfiguration['options']['label']);
            }

            $this->formFieldConfiguration['templateOptions'] = $templateOptions;
        }

        if (isset($this->fieldConfiguration['validation'])) {
            $validation = $this->fieldConfiguration['validation'];
            $notBlankConstraintClass = 'Symfony\Component\Validator\Constraints\NotBlank';

            if (isset($validation[$notBlankConstraintClass])) {
                $constraint = $validation[$notBlankConstraintClass];

                if (isset($constraint['message'])) {
                    $this->formFieldConfiguration['validation']['message']['blank'] = $constraint['message'];
                }
            }

            $regexConstraintClass = 'Symfony\Component\Validator\Constraints\Regex';

            if (isset($validation[$regexConstraintClass])) {
                $constraint = $validation[$regexConstraintClass];
                $this->formFieldConfiguration['templateOptions']['pattern'] = $constraint['pattern'];

                if (isset($constraint['message'])) {
                    $this->formFieldConfiguration['validation']['messages']['regex'] = $constraint['message'];
                }
            }
        }

        $this->formFieldConfiguration['templateOptions']['type'] = $this->getFieldType();

        $this->buildFieldTypeConfiguration();

        return $this->formFieldConfiguration;
    }


    abstract protected function getFieldType();

    protected function buildFieldTypeConfiguration(): void
    {
    }
}
