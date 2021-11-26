<?php declare(strict_types = 1);
namespace noxkiwi\crud\Context;

use noxkiwi\core\Constants\Mvc;
use noxkiwi\core\Context;
use noxkiwi\core\Exception\InvalidArgumentException;
use noxkiwi\crud\Crud;
use noxkiwi\crud\Manipulator;
use noxkiwi\dataabstraction\Model;
use noxkiwi\modal\ModalSetting;
use noxkiwi\modal\ModalSize;
use function explode;
use function is_string;
use const E_ERROR;

/**
 * I am the CRUD backend context.
 *
 * @package      noxkiwi\crud\Context
 *
 * @uses         \noxkiwi\crud\Context\CrudContext::viewList()
 *   -  This is a view that shows the list of known entries of the model.
 *
 * @uses         \noxkiwi\crud\Context\CrudContext::viewCreate()
 *   -  This is a view that shows the empty form for creating a new entry for the model.
 *
 * @author       Jan Nox <jan.nox@pm.me>
 * @license      https://nox.kiwi/license
 * @copyright    2021 noxkiwi
 * @version      1.0.0
 * @link         https://nox.kiwi/
 */
abstract class CrudContext extends Context
{
    /** @var \noxkiwi\dataabstraction\Model I am the Model that will be used to manage data from it. */
    private Model $model;
    /** @var \noxkiwi\crud\Crud I am the Crud class instance that helps generating forms and handling data. */
    private Crud $crud;
    /** @var \noxkiwi\crud\Manipulator $manipulator I am the Manipulator used for this CRUD generator. */
    private Manipulator $manipulator;

    /**
     * CrudContext constructor.
     * @throws \noxkiwi\core\Exception
     * @throws \noxkiwi\core\Exception\ConfigurationException
     * @throws \noxkiwi\core\Exception\InvalidArgumentException
     * @throws \noxkiwi\singleton\Exception\SingletonException
     */
    protected function __construct()
    {
        parent::__construct();
        $modelName = $this->request->get('modelName');
        if (empty($modelName) || ! is_string($modelName)) {
            throw new InvalidArgumentException('YOU_MUST_GIVE_A_MODEL_NAME', E_ERROR);
        }
        $this->model       = self::fetchModel($modelName);
        $this->crud        = new Crud($this->model);
        $this->request->set(Mvc::TEMPLATE, 'json');
        $this->response->set(Mvc::TEMPLATE, 'json');
        $this->setManipulator(new Manipulator($this->getModel(), $this->getCrud()->getConfig()));
    }

    /**
     * I will set the Manipulator.
     * @param \noxkiwi\crud\Manipulator $manipulator
     */
    public function setManipulator(Manipulator $manipulator): void
    {
        $this->manipulator = $manipulator;
    }

    final protected function getCrud(): Crud
    {
        return $this->crud;
    }

    /**
     * @return \noxkiwi\dataabstraction\Model
     */
    final protected function getModel(): Model
    {
        return $this->model;
    }

    /**
     * I will show all known entries.
     */
    final protected function viewList(): void
    {
        $responseRows = [];
        $model        = $this->model;
        $dataSets = $model->search();
        foreach ($dataSets as $dataset) {
            $responseRow    = $this->manipulator->manipulateRow($responseRow ?? [], $dataset);
            $responseRows[] = $responseRow;
        }
        $this->response->set('data', $responseRows);
    }

    public function isAllowed(): bool
    {
        return true;
    }

    /**
     * I will output the CREATE form.
     */
    protected function viewCreate(): void
    {
        $primaryKey = $this->request->get('identifier', '');
        $form       = $this->crud->buildForm($primaryKey);
        $mode       = empty($primaryKey) ? 'create' : 'modify';
        $this->response->set(ModalSetting::BODY, $form->output());
        $this->response->set(ModalSetting::HEAD, $this->translate($mode));
        $this->response->set(ModalSetting::SIZE, ModalSize::X_LARGE);
    }

    /**
     * I will output the CREATE form.
     */
    protected function viewPut(): void
    {
        $primaryKey = $this->request->get($this->model->getPrimarykey(), '');
        $this->crud->edit($primaryKey);
        $this->response->set(ModalSetting::HEAD, $this->translate('CREATE', ['type' => $this->model->getModelName()]));
        $this->response->set(ModalSetting::SIZE, ModalSize::X_LARGE);
    }

    public static function fetchModel(string $modelName): Model
    {
        $modelName     .= 'Model';
        $namespaceInfo = explode('\\Context', static::class);
        $modelName     = "\\{$namespaceInfo[0]}\\Model\\{$modelName}";

        return $modelName::getInstance();
    }
}

