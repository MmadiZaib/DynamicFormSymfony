<?php

declare(strict_types=1);

namespace App\DynamicForm\Mapper;

use App\Exception\FormMapperException;
use App\Exception\NonExistentFormException;
use App\Factory\DynamicFormFactory;
use App\Factory\FormFieldFactory;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class FormMapper
{
    /**
     * @var CsrfTokenManagerInterface
     */
    private $csrfTokenManager;
    /**
     * @var DynamicFormFactory
     */
    private $dynamicFormFactory;
    /**
     * @var FormFieldFactory
     */
    private $formFieldFactory;

    public function __construct(
        CsrfTokenManagerInterface $csrfTokenManager,
        DynamicFormFactory $dynamicFormFactory,
        FormFieldFactory $formFieldFactory
    ) {

        $this->csrfTokenManager = $csrfTokenManager;
        $this->dynamicFormFactory = $dynamicFormFactory;
        $this->formFieldFactory = $formFieldFactory;
    }

    /**
     * @param string $formName
     * @return array
     * @throws FormMapperException
     */
    public function map(string $formName): array
    {
        $dynamicFormConfiguration = [];

        try {
            $configuration = (array) $this->dynamicFormFactory->getConfiguration($formName);
        } catch (NonExistentFormException $e) {
            throw new FormMapperException($e->getMessage());
        }

        if (!empty($configuration)) {
            foreach ($configuration as $fieldName => $fieldConfiguration) {
                $fieldConfiguration['name'] = $fieldName;

                $formField = $this->formFieldFactory->getFormField($fieldConfiguration['type']);
                $formField->setFieldConfiguration($fieldConfiguration);

                $dynamicFormConfiguration[] = $formField->getFormFieldConfiguration();
            }
        }

        $formName = !empty($formName) ? $formName: 'form';

        $token = $this->csrfTokenManager->refreshToken($formName);

        $tokenFieldConfiguration = [
            'key' => '_token',
            'type' => 'hidden',
            'defaultValue' => $token->getValue(),
        ];

        $dynamicFormConfiguration[] = $tokenFieldConfiguration;

        return $dynamicFormConfiguration;
    }
}
