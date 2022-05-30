<?php

declare(strict_types=1);

namespace App\Factory;

use App\DynamicForm\FormField\BirthDayField;
use App\Exception\NonExistentFormException;
use App\Exception\NotExistentDataProviderException;
use App\Provider\DataProvider;
use App\Provider\HelperMessageProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DynamicFormFactory
{
    /** @var FormFactoryInterface  */
    protected $formFactory;

    /** @var array */
    protected $configuration;

    /** @var DataProvider[]  */
    protected $dataProviders = [];

    /** @var HelperMessageProvider[] */
    protected $helpMessageProviders = [];

    /** @var array  */
    protected $eventSubscribers = [];

    public function __construct(FormFactoryInterface $formFactory, $dynamicFormConfig)
    {
        $this->formFactory = $formFactory;
        $this->configuration = $dynamicFormConfig;
    }

    public function addDataProvider($alias, DataProvider $dataProvider): void
    {
        $this->dataProviders[$alias] = $dataProvider;
    }

    public function addHelpMessageProvider($alias, HelperMessageProvider $helperMessageProvider): void
    {
        $this->helpMessageProviders[$alias] = $helperMessageProvider;
    }


    public function addEventSubscriber(string $formName, EventSubscriberInterface $eventSubscriber): void
    {
        if (!isset($this->eventSubscribers[$formName])) {
            $this->eventSubscribers[$formName] = [];
        }

        $this->eventSubscribers[$formName][] = $eventSubscriber;
    }

    /**
     * @param string $type
     * @param array $data
     * @param array $options
     * @param null $name
     * @return FormInterface
     * @throws NonExistentFormException
     * @throws NotExistentDataProviderException
     */
    public function createForm(string $type, $data = [], array $options = [], $name = null): FormInterface
    {
        return $this->createBuilder($type, $data, $options, $name)->getForm();
    }

    /**
     * This method generates a form based on the configuration file
     *
     * @param string $type
     * @param array $data
     * @param array $options
     * @param null $name
     * @return FormBuilderInterface
     * @throws NonExistentFormException
     * @throws NotExistentDataProviderException
     */
    public function createBuilder(string $type, $data = [], $options = [], $name = null): FormBuilderInterface
    {
        if (!isset($this->configuration[$type])) {
            throw new NonExistentFormException(sprintf('The form "%s" was not found.', $type));
        }

        //dd($type, $name);

        $formBuilder = $this->formFactory->createNamedBuilder($name ?: $type, FormType::class, $data, $options);

        if (isset($this->eventSubscribers[$type])) {
            foreach ($this->eventSubscribers[$type] as $eventSubscriber) {
                $formBuilder->addEventSubscriber($eventSubscriber);
            }
        }

        foreach ($this->configuration[$type] as $type => $fieldConfiguration) {
            if (!$fieldConfiguration['enabled']) {
                continue;
            }

            $fieldOptions = $fieldConfiguration['options'] ?? [];

            if (isset($fieldConfiguration['data_provider'])) {
                $fieldOptions['choices'] = $this->loadDataProvider($fieldConfiguration['data_provider'])->getData();
            }

            if (isset($fieldConfiguration['help_message_provider'])) {
                $fieldOptions['help'] = $this->loadHelpMessageProvider($fieldConfiguration['help_message_provider'])->getHelpMessage($fieldOptions['help']);
            }

            if (isset($fieldConfiguration['validation'])) {
                $constraints = [];

                foreach ($fieldConfiguration['validation'] as $validatorName => $options) {
                    $constraints[] = new $validatorName($options);
                }

                $fieldOptions['constraints'] = $constraints;
            }

            if (isset($fieldConfiguration['type'])) {
                $fieldOptions = $this->updateBirthDayFieldOptions($fieldConfiguration['type'], $type, $fieldOptions);
            }

            $field = $formBuilder->create($type, $fieldConfiguration['type'], $fieldOptions);

            if (isset($fieldConfiguration['transformer'])) {
                $transformConfiguration = $fieldConfiguration['transformer'];
                $transformer = new $transformConfiguration['class']();

                if (isset($transformConfiguration['calls'])) {
                    foreach ($transformConfiguration['calls'] as $call) {
                        call_user_func($transformer, $call[0], $call[1]);
                    }
                }

                $field->addModelTransformer($transformer);
            }

            $formBuilder->add($field);
        }

        return $formBuilder;
    }

    /**
     * @param string $alias
     * @return DataProvider
     * @throws NotExistentDataProviderException
     */
    public function loadDataProvider(string $alias): DataProvider
    {
        if (!isset($this->dataProviders[$alias])) {
            throw new NotExistentDataProviderException();
        }

        return $this->dataProviders[$alias];
    }

    /**
     * @param string $alias
     * @return HelperMessageProvider
     * @throws NotExistentDataProviderException
     */
    public function loadHelpMessageProvider(string $alias): HelperMessageProvider
    {
        if (!isset($this->dataProviders[$alias])) {
            throw new NotExistentDataProviderException();
        }

        return $this->helpMessageProviders[$alias];
    }

    public function updateBirthDayFieldOptions(string $type, string $key, array $fieldOptions): array
    {
        if ($type  != BirthdayType::class) {
            return $fieldOptions;
        }

        $birthdayField = new BirthDayField();
        $birthdayField->setFieldConfiguration([
            'name' => $key,
            'type' => $type,
            'options' => $fieldOptions,
        ]);

        $fieldOptions = $birthdayField->getFormFieldConfiguration()['templateOptions'];
        $fieldOptions['label'] = lcfirst($fieldOptions['label']);
        unset($fieldOptions['type']);

        return $fieldOptions;
    }

    /**
     * @param string $key
     * @param $object
     * @return ValidatorInterface
     * @throws NonExistentFormException
     */
    public function createValidator(string $key, $object): ValidatorInterface
    {
        if (!isset($this->configuration)) {
            throw new NonExistentFormException(sprintf('The form "%s" was not found.', $key));
        }

        $validator = Validation::createValidatorBuilder()->getValidator();
        /** @var ClassMetadata $metadata */
        $metadata = $validator->getMetadataFor(get_class($object));

        foreach ($this->configuration[$key] as $key => $fieldConfiguration) {
            if (!$fieldConfiguration['enabled']) {
                continue;
            }

            if (!$fieldConfiguration['validation']) {
                continue;
            }

            foreach ($fieldConfiguration['validation'] as $validatorName => $options) {
                $metadata->addPropertyConstraint($key, new $validatorName($options));
            }
        }

        return $validator;
    }


    /**
     * @param string|null $name
     * @return array|mixed
     * @throws NonExistentFormException
     */
    public function getConfiguration(?string $name = null): array
    {
        if ($name === null) {
            return $this->configuration;
        }

        if (!$this->$this->has($name)) {
            throw new NonExistentFormException();
        }

        return $this->configuration[$name];
    }

    /**
     * Check if a given form exists.
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool {
        return isset($this->configuration[$name]);
    }
}
