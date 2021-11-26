<?php declare(strict_types = 1);
namespace noxkiwi\crud;

use Exception;
use noxkiwi\cache\Cache;
use noxkiwi\core\Config;
use noxkiwi\core\Config\JsonConfig;
use noxkiwi\core\Constants\Mvc;
use noxkiwi\core\ErrorHandler;
use noxkiwi\core\Exception as CoreException;
use noxkiwi\core\Exception\InvalidArgumentException;
use noxkiwi\core\Helper\LinkHelper;
use noxkiwi\core\Helper\StringHelper;
use noxkiwi\core\Helper\WebHelper;
use noxkiwi\core\Request;
use noxkiwi\core\Response;
use noxkiwi\core\Traits\LanguageImprovementTrait;
use noxkiwi\core\Traits\TranslationTrait;
use noxkiwi\crud\Constants\DataType;
use noxkiwi\crud\Constants\DisplayType;
use noxkiwi\crud\Interfaces\CrudInterface;
use noxkiwi\dataabstraction\Entry;
use noxkiwi\dataabstraction\FieldDefinition;
use noxkiwi\dataabstraction\Model;
use noxkiwi\formbuilder\Field;
use noxkiwi\formbuilder\Fieldset;
use noxkiwi\formbuilder\Form;
use noxkiwi\log\Traits\LogTrait;
use noxkiwi\modal\ModalSetting;
use noxkiwi\modal\ModalSize;
use function class_exists;
use function end;
use function explode;
use function in_array;
use function is_array;
use function str_ends_with;
use function str_replace;
use function strtolower;
use function uniqid;
use const E_USER_NOTICE;

/**
 * I am
 *
 * @package      noxkiwi\crud
 * @author       Jan Nox <jan.nox@pm.me>
 * @license      https://nox.kiwi/license
 * @copyright    2020 - 2021 nox.kiwi
 * @version      1.0.1
 * @link         https://nox.kiwi/
 */
final class Crud implements CrudInterface
{
    use LanguageImprovementTrait;
    use LogTrait;
    use TranslationTrait;

    public const        COLUMN_ACTION           = 'action';
    private const       FIELD                   = 'field';
    private const       FOREIGN                 = 'foreign';
    private const       MODEL                   = 'model';
    public const        PARAM_CONFIRM           = 'cnfrm';
    public const        DATATYPE_TEXT_TIMESTAMP = 'text_timestamp';
    public const        RESPONSE_PRIMARY        = 'primaryKey';
    public const        DISABLED_FIELDS         = 'disabled';
    private const       VIEW_SAVEERROR          = 'saveerror';
    /** @var \noxkiwi\dataabstraction\Model Contains the model this CRUD instance is based upon */
    private Model $model;
    /** @var \noxkiwi\formbuilder\Field[] */
    private array $fieldList;
    /** @var \noxkiwi\dataabstraction\Entry I am the Entry instance that is used for the edit view. */
    private Entry $entry;
    /** @var \noxkiwi\core\Config Contains an instance of config storage */
    private Config $config;
    /** @var \noxkiwi\core\Request I am the Request. */
    private Request $request;
    /** @var \noxkiwi\core\Response I am the Response. */
    private Response $response;

    /**
     * Creates the instance and sets the $model of this instance. Also creates the Form instance
     *
     * @param model $model
     *
     * @throws \noxkiwi\core\Exception
     * @throws \noxkiwi\core\Exception\ConfigurationException If the Model is not capable of CRUD
     * @throws \noxkiwi\core\Exception\InvalidArgumentException
     * @throws \noxkiwi\singleton\Exception\SingletonException
     */
    public function __construct(Model $model)
    {
        $this->model    = $model;
        $this->request  = Request::getInstance();
        $this->response = Response::getInstance();
        $this->response->set(self::RESPONSE_PRIMARY, $this->model->getPrimarykey());
        $this->getConfig();
    }

    /**
     * I will create a config instance for this Crud generator.
     *
     * @throws \noxkiwi\core\Exception
     * @throws \noxkiwi\core\Exception\ConfigurationException
     * @throws \noxkiwi\core\Exception\InvalidArgumentException
     * @throws \noxkiwi\singleton\Exception\SingletonException
     * @return Config
     */
    public function getConfig(): Config
    {
        if (! empty($this->config)) {
            return $this->config;
        }
        $config = Cache::getInstance()->get(
            Cache::DEFAULT_PREFIX . '_CONFIG_CRUD',
            $this->model::SCHEMA . '_' . $this->model::TABLE
        );
        if (! empty($config) && is_array($config)) {
            return $this->config = new Config($config);
        }
        $file   = 'config/crud/' . $this->model::SCHEMA . '_' . $this->model::TABLE . '.json';
        $config = new JsonConfig($file, true);
        Cache::getInstance()->set(
            Cache::DEFAULT_PREFIX . '_CONFIG_CRUD',
            $this->model::SCHEMA . '_' . $this->model::TABLE,
            $config->get()
        );
        $this->config = $config;

        return $this->config;
    }

    public static function fetchModel(string $modelName): Model
    {
        $modelName     .= 'Model';
        $namespaceInfo = explode('\\Context', self::class);
        $modelName     = "\\$namespaceInfo[0]\\Model\\$modelName";

        return $modelName::getInstance();
    }

    /**
     * Returns the private model of this instance
     * @return       Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * I will return whether the given $fieldName is among forbidden fields.
     * Those are, among others, creation/modification timestamps and the primary key.
     *
     * @param string $fieldName
     *
     * @return bool
     */
    private function isForbidden(string $fieldName): bool
    {
        if (! empty($this->entry)) {
            return false;
        }
        if ($fieldName === $this->getModel()->getPrimarykey()) {
            return true;
        }
        if (str_ends_with($fieldName, '_created')) {
            return true;
        }
        if (str_ends_with($fieldName, '_modified')) {
            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(int|string|null $primaryKey = null, bool $addSubmitButton = null): Form
    {
        $form = new Form([
                             Form::FORM_ACTION => LinkHelper::makeUrl([Mvc::VIEW => 'put']),
                             Form::FORM_METHOD => WebHelper::METHOD_POST,
                             'form_id'         => $this->model->getPrimarykey()
                         ]);
        $this->useEntry($primaryKey);
        $fieldSet = new Fieldset([]);
        $fieldSet->addField(
            new Field([
                          Field::FIELD_NAME        => 'modelName',
                          Field::FIELD_TITLE       => 'modelName',
                          Field::FIELD_DESCRIPTION => 'modelName',
                          Field::FIELD_DOMID       => 'modelName',
                          Field::FIELD_TYPE        => 'hidden',
                          Field::FIELD_VALUE       => $this->model->getModelName()
                      ])
        );
        foreach ($this->model->getDefinitions() as $fieldName => $fieldDefinition) {
            if ($this->isForbidden($fieldName)) {
                continue;
            }
            if ($this->isDisabled($fieldName)) {
                $fieldDefinition->readonly = true;
            }
            $fields = $this->decideField($fieldDefinition);
            foreach ($fields as $field) {
                $fieldSet->addField($field);
            }
        }
        if ($addSubmitButton ?? true) {
            $fieldSet->addField(
                new Field([
                              Field::FIELD_NAME        => 'name',
                              Field::FIELD_TITLE       => 'submit',
                              Field::FIELD_DESCRIPTION => 'description',
                              Field::FIELD_DOMID       => 'submit',
                              Field::FIELD_TYPE        => 'submit',
                              Field::FIELD_VALUE       => $this->translate('CRUD.SAVE')
                          ])
            );
        }
        $form->addFieldset($fieldSet);

        return $form;
    }

    /**
     * I will generate one or more fields fitting to the given $fieldDefinition, based on the type of the field.
     *
     * @param \noxkiwi\dataabstraction\FieldDefinition $fieldDefinition
     *
     * @return \noxkiwi\formbuilder\Field[]
     */
    private function decideField(FieldDefinition $fieldDefinition): array
    {
        if (str_ends_with(strtolower($fieldDefinition->name), '_flags')) {
            return $this->buildFlagFields($fieldDefinition);
        }

        return [$this->buildField($fieldDefinition)];
    }

    /**
     * I will load this one element to make editing of it possible.
     *
     * @param mixed $primaryKey
     *
     * @return       \noxkiwi\crud\Crud
     */
    public function useEntry($primaryKey = null): Crud
    {
        if (empty($primaryKey)) {
            $this->response->set('CRUD_FEEDBACK', 'ENTRY_CREATE');

            return $this;
        }
        $this->response->set('CRUD_FEEDBACK', 'ENTRY_UPDATE');
        $entry = $this->model->loadEntry($primaryKey);
        if ($entry === null) {
            return $this;
        }
        $this->entry = $entry;

        return $this;
    }

    /**
     * I will return whether the given $fieldName is disabled for CRUD.
     *
     * @param string $fieldName
     *
     * @return bool
     */
    private function isDisabled(string $fieldName): bool
    {
        if ($this->getModel()->getPrimarykey() === $fieldName) {
            return true;
        }
        if (in_array(
            $fieldName,
            [
                $this->model::TABLE . '_id',
                $this->model::TABLE . '_modified',
                $this->model::TABLE . '_created'
            ],
            true
        )) {
            return true;
        }
        try {
            return in_array(
                $fieldName,
                $this->getDisabledFields(),
                true
            );
        } catch (Exception $exception) {
            ErrorHandler::handleException($exception, E_USER_NOTICE);

            return true;
        }
    }

    /**
     * @throws \noxkiwi\core\Exception
     * @throws \noxkiwi\core\Exception\ConfigurationException
     * @throws \noxkiwi\core\Exception\InvalidArgumentException
     * @throws \noxkiwi\singleton\Exception\SingletonException
     * @return array
     */
    private function getDisabledFields(): array
    {
        return $this->getConfig()->get(self::DISABLED_FIELDS, []);
    }

    /**
     * I will create a Field instance from the given Field definition.
     *
     * @param \noxkiwi\dataabstraction\FieldDefinition $fieldDefinition
     *
     * @return \noxkiwi\formbuilder\Field
     */
    private function buildField(FieldDefinition $fieldDefinition): Field
    {
        // DEFINE THE FIELD
        $fieldData = [
            Field::FIELD_DOMID       => uniqid($fieldDefinition->name, false),
            Field::FIELD_NAME        => $fieldDefinition->name,
            Field::FIELD_TITLE       => $this->translate($fieldDefinition->name . '_TITLE'),
            Field::LABEL             => $this->translate($fieldDefinition->name . '_LABEL'),
            Field::FIELD_DESCRIPTION => $this->translate($fieldDefinition->name . '_DESCRIPTION'),
            Field::FIELD_VALIDATOR   => $fieldDefinition->type,
            Field::FIELD_TYPE        => self::getDisplayType($fieldDefinition),
            Field::FIELD_REQUIRED    => $fieldDefinition->required,
            Field::FIELD_PLACEHOLDER => $this->translate(($fieldDefinition->displayName ?? $fieldDefinition->name) . '_PLACEHOLDER'),
            Field::FIELD_MULTIPLE    => false,
            Field::FIELD_READONLY    => $fieldDefinition->readonly,
            Field::FIELD_VALUE       => ''
        ];
        if ($fieldDefinition->foreign) {
            $fieldData = self::doForeign($fieldDefinition, $fieldData);
        }
        if (in_array($fieldDefinition->displayType, [DisplayType::SELECT, DisplayType::SELECT_MULTIPLE], true)) {
            $fieldData[Field::FIELD_ELEMENTS] = [];
            if (! empty($fieldDefinition->enum)) {
                $class = $fieldDefinition->enum;
                if (class_exists($class)) {
                    $a        = explode('\\', $class);
                    $b        = end($a);
                    $enumName = str_replace('Validator', '', $b);
                    foreach ($class::ENUMERATION as $display => $value) {
                        $fieldData[Field::FIELD_ELEMENTS][$value] = [
                            'value'   => $value,
                            'display' => $this->translate("{$enumName}_$display")
                        ];
                    }
                }
            }
        }
        // NOW ADD DATA
        if (! empty($this->entry)) {
            $fieldData[Field::FIELD_VALUE] = $this->entry->{$fieldDefinition->name};
        }
        if (isset($fieldDefinition->foreign['type'])) {
            $fieldData[Field::FIELD_MULTIPLE] = true;
            if ($fieldDefinition->foreign['type'] !== 'json') {
                $fieldData[Field::FIELD_MULTIPLE] = true;
                $fieldData[Field::FIELD_VALUE]    = explode(',', (string)($fieldData[Field::FIELD_VALUE] ?? ''));
            }
        }

        return new Field($fieldData);
    }

    /**
     * I will generate a set of checkboxes matching for the Flags available in the current Model.
     *
     * @param \noxkiwi\dataabstraction\FieldDefinition $fieldDefinition
     *
     * @return array
     */
    private function buildFlagFields(FieldDefinition $fieldDefinition): array
    {
        $flags  = $this->model->getConfig()->get('flags', []);
        $fields = [];
        if (empty($flags)) {
            return $fields;
        }
        $flagData = $this->entry->{$fieldDefinition->name} ?? null;
        foreach ($flags as $flagValue => $flagName) {
            $fieldData = [
                Field::FIELD_DOMID       => uniqid($fieldDefinition->name, false),
                Field::FIELD_NAME        => "$fieldDefinition->name[$flagValue]",
                Field::FIELD_TITLE       => $this->translate($fieldDefinition->name . '_' . $flagName . '_TITLE'),
                Field::LABEL             => $this->translate($fieldDefinition->name . '_' . $flagName . '_LABEL'),
                Field::FIELD_DESCRIPTION => $this->translate($fieldDefinition->name . '_' . $flagName . '_DESCRIPTION'),
                Field::FIELD_VALIDATOR   => $fieldDefinition->type,
                Field::FIELD_TYPE        => 'checkbox',
                Field::FIELD_REQUIRED    => false,
                Field::FIELD_MULTIPLE    => false,
                Field::FIELD_READONLY    => $fieldDefinition->readonly,
                Field::FIELD_VALUE       => $this->model->isFlag($flagValue, (int)$flagData)
            ];
            $field     = new Field($fieldData);
            $fields[]  = $field;
        }

        return $fields;
    }

    /**
     * I will generate remote elements to mimic foreign relationships.
     *
     * @param \noxkiwi\dataabstraction\FieldDefinition $fieldDefinition
     * @param mixed                                    $fieldData
     *
     * @return array
     */
    private static function doForeign(FieldDefinition $fieldDefinition, mixed $fieldData): array
    {
        $fieldData[Field::FIELD_TYPE]     = 'select';
        $fieldData[Field::FIELD_ELEMENTS] = [];
        $model                            = $fieldDefinition->foreign['model']::getInstance();
        $model->search();
        $rows                             = $model->getResult();
        foreach ($rows as $row) {
            $value                                    = $row[$fieldDefinition->foreign['value']];
            $fieldData[Field::FIELD_ELEMENTS][$value] = [
                'value'   => $value,
                'display' => StringHelper::interpolate($fieldDefinition->foreign['display'], $row)
            ];
        }

        return $fieldData;
    }

    /**
     * Resolve a datatype to a foreced display type
     *
     * @param \noxkiwi\dataabstraction\FieldDefinition $fieldDefinition
     *
     * @return       string
     */
    protected static function getDisplayType(FieldDefinition $fieldDefinition): string
    {
        if (in_array($fieldDefinition->displayType, DisplayType::ALL, true)) {
            return $fieldDefinition->displayType;
        }
        switch ($fieldDefinition->type) {
            case DataType::TEXT:
                $displayType = DisplayType::DEFAULT;
                break;
            case DataType::NUMBER:
            case DataType::NUMBER_NATURAL:
                $displayType = DisplayType::NUMBER;
                break;
            case self::DATATYPE_TEXT_TIMESTAMP:
                $displayType = 'timestamp';
                break;
            default:
                return DisplayType::DEFAULT;
        }

        return $displayType;
    }

    /**
     * @inheritDoc
     */
    public function bulkDelete(): void
    {
    }

    /**
     * @inheritDoc
     *
     * @param mixed $primaryKey
     */
    public function edit(string|int $primaryKey): void
    {
        $form = $this->buildForm($primaryKey);
        if (! $form->isSent()) {
            $this->response->set('form', $form->output());

            return;
        }
        $formData = $form->get();
        $data     = $this->model->normalizeData($formData);
        try {
            if (! empty($primaryKey)) {
                $checkEntry = $this->model->loadEntry($primaryKey);
                if ($checkEntry === null) {
                    return;
                }
                $this->entry = $checkEntry;
            } else {
                $this->entry = $this->model->getEntry();
            }
            if (empty($this->entry)) {
                return;
            }
            $this->entry->set($data);
        } catch (InvalidArgumentException $exception) {
            $errors = [];
            foreach ($exception->getInfo() as $validationError) {
                if (! $validationError instanceof CoreException) {
                    continue;
                }
                $errors[] = [
                    'fieldName' => $validationError->getInfo()['fieldName'],
                    'code'      => $this->translate($validationError->getInfo()['errors'][0]['code']),
                    'info'      => $validationError->getInfo()['errors'][0]['info'],
                    'message'   => $validationError->getMessage(),
                ];
            }
            $this->response->set('errors', $errors);
            $this->response->set(Mvc::VIEW, self::VIEW_SAVEERROR);

            return;
        }
        try {
            $errors = $this->model->validate($this->entry->get());
            if (! empty($errors)) {
                $this->response->set('errors', $errors);
                $this->response->set(Mvc::VIEW, self::VIEW_SAVEERROR);

                return;
            }
            $this->entry->save();
        } catch (Exception $exception) {
            ErrorHandler::handleException($exception);
            $this->response->set(Mvc::VIEW, self::VIEW_SAVEERROR);

            return;
        }
        $this->response->set(Mvc::VIEW, 'crudsuccess');
    }

    /**
     * @inheritDoc
     */
    public function delete(string|int $primaryKey): void
    {
        $this->show($primaryKey);
    }

    /**
     * @inheritDoc
     */
    public function show(string|int $primaryKey): void
    {
        $this->response->set(
            'form',
            $this->buildForm($primaryKey)->output()
        );
    }

    /**
     * @inheritDoc
     */
    public function duplicate($primaryKey): void
    {
        $this->show($primaryKey);
    }

    /**
     * @inheritDoc
     */
    public function create(): void
    {
        try {
            $this->response->set(Mvc::CONTEXT, 'crud');
            $form = $this->buildForm();
            Hook::getInstance()->fire(Hook::CRUD_CREATE_FORM_INIT, $form);
            if (! $form->isSent()) {
                $this->response->set(ModalSetting::BODY, $form->output());
                $this->response->set(ModalSetting::HEAD, $this->translate('CREATE', ['type' => $this->model->getModelName()]));
                $this->response->set(ModalSetting::SIZE, ModalSize::MEDIUM);

                return;
            }
            $data = $this->model->normalizeData($this->request->get());
            Hook::getInstance()->fire(Hook::CRUD_CREATE_BEFORE_SAVE, $data);
            $errors = $this->model->validate($data);
            Hook::getInstance()->fire(Hook::CRUD_CREATE_AFTER_VALIDATION, $data);
            if (! empty($errors)) {
                $this->response->set('errors', $errors);
                $this->response->set(Mvc::VIEW, self::VIEW_SAVEERROR);

                return;
            }
            Hook::getInstance()->fire(Hook::CRUD_CREATE_BEFORE_SAVE, $data);
            $this->model->save($data);
            Hook::getInstance()->fire(Hook::CRUD_CREATE_SUCCESS, $data);
            $this->response->set(Mvc::VIEW, 'crudsuccess');
        } catch (Exception $exception) {
            ErrorHandler::handleException($exception);
        }
    }
}
