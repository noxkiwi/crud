<?php declare(strict_types = 1);
namespace noxkiwi\crud\Context;

use noxkiwi\core\Config\JsonConfig;
use noxkiwi\core\Constants\Mvc;
use noxkiwi\core\Context;
use noxkiwi\core\Exception\InvalidArgumentException;
use noxkiwi\core\Helper\FrontendHelper;
use noxkiwi\core\Helper\LinkHelper;
use noxkiwi\core\Path;
use noxkiwi\core\Response;
use noxkiwi\crud\Crud;
use noxkiwi\dataabstraction\Model;
use noxkiwi\dataabstraction\Validator\Text\ModelfieldValidator;
use function explode;
use function in_array;
use const E_ERROR;

/**
 * I am
 *
 * @package      noxkiwi\crud
 * @author       Jan Nox <jan.nox@pm.me>
 * @license      https://nox.kiwi/license
 * @copyright    2020 noxkiwi
 * @version      1.0.0
 * @link         https://nox.kiwi/
 */
abstract class CrudfrontendContext extends Context
{
    public const PARAM_MODEL_NAME = 'modelName';
    /** @var string */
    private string $modelName;

    /**
     * Crudfrontend Context constructor.
     * @throws \noxkiwi\core\Exception\InvalidArgumentException
     * @throws \noxkiwi\singleton\Exception\SingletonException
     */
    protected function __construct()
    {
        parent::__construct();
        $modelName = $this->request->get(self::PARAM_MODEL_NAME);
        $errors    = ModelfieldValidator::getInstance()->validate($modelName);
        if (! empty($errors)) {
            throw new InvalidArgumentException('MUST_PASS_MODEL_NAME', E_ERROR, $errors);
        }
        $this->response->set('entity', $this->translate($modelName));
        $this->modelName = $modelName;
    }

    /**
     * I will solely show the CRUD list using DataTables.
     */
    public function viewList(): void
    {
        $model = self::fetchModel($this->request->get(self::PARAM_MODEL_NAME));
        $this->response->set(Mvc::TEMPLATE, 'manager');
        $this->response->set(Mvc::CONTEXT, $this->request->get(Mvc::CONTEXT));
        $this->response->set(Mvc::VIEW, $this->request->get(Mvc::VIEW));
        $this->response->set('config', $this->getCrudConfig($model));
        $content = FrontendHelper::parseFile('/var/www/_dev/vendor/noxkiwi/crud/frontend/view/crudfrontend/list.php');
        $this->response->set('content', $content);
        $this->response->set(self::PARAM_MODEL_NAME, $this->modelName);
    }

    /**
     * I will return the desired Model instance.
     *
     * @param string $modelName
     *
     * @throws \noxkiwi\singleton\Exception\SingletonException
     * @return \noxkiwi\dataabstraction\Model
     */
    public static function fetchModel(string $modelName): Model
    {
        $modelName     .= 'Model';
        $namespaceInfo = (array)explode('\\Context', static::class);
        /** @var \noxkiwi\dataabstraction\Model $modelPointer */
        $modelPointer = "\\{$namespaceInfo[0]}\\Model\\{$modelName}";

        return $modelPointer::getInstance();
    }

    /**
     * I will solely return the config array for the given $model.
     *
     * @param \noxkiwi\dataabstraction\Model $model
     *
     * @throws \noxkiwi\core\Exception\ConfigurationException
     * @throws \noxkiwi\core\Exception\InvalidArgumentException
     * @throws \noxkiwi\singleton\Exception\SingletonException
     * @return array
     */
    private function getCrudConfig(Model $model): array
    {
        $crudConfig        = new JsonConfig(\noxkiwi\crud\Path::CONFIG_CRUD . $model::SCHEMA . '_' . $model::TABLE . '.json');
        $constructorObject = [
            'autoWidth'     => true,
            'deferRender'   => true,
            'info'          => true,
            'lengthChange'  => true,
            'ordering'      => true,
            'paging'        => false,
            'processing'    => true,
            #   'scrollX' => true,
            'scrollY'       => 600,
            'searching'     => true,
            'oSearch'       => ['sSearch' => $this->request->get('q', '')],
            'serverSide'    => false,
            'stateSave'     => true,
            'ajax'          => LinkHelper::get([Mvc::CONTEXT => 'crud', Mvc::VIEW => 'list', 'modelName' => $this->modelName]),
            'dom'           => 'Bfrtip',
            'orderMulti'    => true,
            'renderer'      => 'bootstrap',
            'rowId'         => 'opcitem_id',
            'scollCollapse' => true,
            'searchDelay'   => 250,
            'colReorder'    => [
                'enable'   => true,
                'realtime' => true
            ],
            'fixedColumns'  => false,
            'fixedHeader'   => false,
            'responsive'    => false,
            'select'        => [
                'style'    => 'os',
                'blurable' => true,
                'selector' => 'tr td:not(.noVis)'
            ],
            'buttons'       => [
                [
                    'extend'  => 'colvis',
                    'columns' => ':not(.noVis)'
                ]
            ],
            'columns'       => [
                [
                    'data'           => Crud::COLUMN_ACTION,
                    'name'           => Crud::COLUMN_ACTION,
                    'defaultContent' => '-',
                    'visible'        => true,
                    'type'           => 'num',
                    'className'      => 'text-left',
                    'render'         => [
                        '_'      => 'display',
                        'sort'   => 'sort',
                        'filter' => 'filter'
                    ]
                ]
            ],
        ];
        // Add fields
        $fieldNames    = $model->getFieldNames();
        $visibleFields = $crudConfig->get('fields>show', []);
        if (empty($visibleFields)) {
            $visibleFields = $fieldNames;
        }
        foreach ($fieldNames as $fieldName) {
            $constructorObject['columns'][] = [
                'name'           => $fieldName,
                'data'           => $fieldName,
                'defaultContent' => '-',
                'visible'        => in_array($fieldName, $visibleFields, true),
                'type'           => 'num',
                'className'      => 'text-left',
                'render'         => [
                    '_'      => 'display',
                    'sort'   => 'sort',
                    'filter' => 'filter'
                ]
            ];
        }

        return $constructorObject;
    }

    /**
     * @inheritDoc
     * @noinspection PhpMissingParentCallCommonInspection
     */
    protected function doShow(Response $response): void
    {
        // Template
        $templateFile    = Path::TEMPLATE_DIR . 'manager' . '/' . Path::TEMPLATE_FILE;
        $templatePath    = Path::getInheritedPath($templateFile);
        $templateContent = FrontendHelper::parseFile($templatePath, $response);
        $response->setOutput($templateContent);
    }
}

