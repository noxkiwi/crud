<?php declare(strict_types = 1);
namespace noxkiwi\crud;

use noxkiwi\core\Config;
use noxkiwi\core\Constants\Mvc;
use noxkiwi\core\Helper\LinkHelper;
use noxkiwi\crud\Frontend\Cell;
use noxkiwi\crud\Interfaces\ManipulatorInterface;
use noxkiwi\dataabstraction\Model;
use noxkiwi\frontend\Element\Badge;
use noxkiwi\frontend\Element\Icon;
use noxkiwi\frontend\Tag\Html\Anchor;
use noxkiwi\frontend\Tag\HtmlTag;
use noxkiwi\translator\Traits\TranslatorTrait;
use noxkiwi\translator\Translator;
use function end;
use function explode;
use function get_class;
use function method_exists;
use function str_replace;
use function trim;

/**
 * I am the basic Manipulator class
 *
 * @package      noxkiwi\crud
 * @author       Jan Nox <jan.nox@pm.me>
 * @license      https://nox.kiwi/license
 * @copyright    2020 nox.kiwi
 * @version      1.0.0
 * @link         https://nox.kiwi/
 */
class Manipulator implements ManipulatorInterface
{
    use TranslatorTrait;

    private Model  $model;
    private Config $config;

    /**
     * @param \noxkiwi\dataabstraction\Model $model
     * @param \noxkiwi\core\Config           $config
     */
    public function __construct(Model $model, Config $config)
    {
        $this->config = $config;
        $this->model  = $model;
    }

    /**
     * @return \noxkiwi\core\Config
     */
    final protected function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * @return \noxkiwi\dataabstraction\Model
     */
    final protected function getModel(): Model
    {
        return $this->model;
    }

    /**
     * I will manipulate the current row of the Crud List.
     *
     * @param array $responseRow
     * @param array $dataset
     *
     * @return array
     */
    public function manipulateRow(array $responseRow, array $dataset): array
    {
        foreach ($this->getModel()->getFieldNames() as $fieldName) {
            $responseRow[$fieldName] = $this->manipulateField($fieldName, $dataset);
        }
        $responseRow[Crud::COLUMN_ACTION] = $this->buildActions($responseRow, $dataset);

        return $responseRow;
    }

    /**
     * I will build an Action.
     *
     * @param array $action
     * @param array $responseRow
     * @param array $dataset
     *
     * @return \noxkiwi\frontend\Tag\HtmlTag
     */
    protected function buildAction(array $action, array $responseRow, array $dataset): HtmlTag
    {
        $action['link']['modelName']                        = $this->getModel()->getModelName();
        $action['link']['identifier']                       = $dataset[$this->getModel()->getPrimarykey()];
        $action['link'][$this->getModel()->getPrimarykey()] = $dataset[$this->getModel()->getPrimarykey()];
        $url                                                = LinkHelper::get($action['link']);
        $editButton                                         = new Action();
        $editButton->setTarget($action['target'] ?? Anchor::TARGET_TOP);
        $editButton->setHref($url);
        $editButton->add(Icon::getFromName($action['icon']));

        return $editButton;
    }

    /**
     * I will build all actions for the current $responseRow.
     *
     * @param array $responseRow
     * @param array $dataset
     *
     * @return \noxkiwi\crud\Frontend\Cell
     */
    protected function buildActions(array $responseRow, array $dataset): Cell
    {
        $actions = $this->getConfig()->get('action>element', []);
        $content = '';
        foreach ($actions as $action) {
            $content .= $this->buildAction($action, $responseRow, $dataset);
        }

        $cell = new Cell();
        $cell->sort = $content;
        $cell->display = $content;
        $cell->filter = $content;
        $cell->export = $content;
        return $cell;
    }

    /**
     * I am the standard manipulation for primary fields.
     *
     * @param string $fieldName
     * @param array  $dataset
     *
     * @return \noxkiwi\crud\Frontend\Cell
     */
    private function manipulatePrimary(string $fieldName, array $dataset): Cell
    {
        $cell          = new Cell();
        $cell->order   = $dataset[$fieldName] ?? '';
        $cell->display = (string)$dataset[$fieldName];
        $cell->filter  = 'primary_' . $dataset[$fieldName] . '_' ?? '';
        $cell->export  = $cell->order;

        return $cell;
    }

    private array $foundManipulators;

    /**
     * I will return whether there is a manipulator method for the given $fieldName.
     *
     * @param string $fieldName
     *
     * @return bool
     */
    private function manipulatorFound(string $fieldName): bool
    {
        // Cache this in the instance for less CPU.
        if (! isset($this->foundManipulators[$fieldName])) {
            $this->foundManipulators[$fieldName] = method_exists($this, "manipulate$fieldName");
        }

        return $this->foundManipulators[$fieldName];
    }

    /**
     * @inheritDoc
     */
    public function manipulateField(string $fieldName, array $dataset): Cell
    {
        if ($this->manipulatorFound($fieldName)) {
            return $this->{"manipulate$fieldName"}($fieldName, $dataset);
        }
        if ($this->getModel()->getPrimarykey() === $fieldName) {
            return $this->manipulatePrimary($fieldName, $dataset);
        }
        $cell          = new Cell();
        $cell->sort    = $dataset[$fieldName] ?? '';
        $cell->display = $dataset[$fieldName] ?? '';
        $cell->filter  = $dataset[$fieldName] ?? '';
        $cell->export  = $dataset[$fieldName] ?? '';

        return $cell;
    }

    /**
     * @inheritDoc
     */
    public function manipulateDatasets(array $datasets): array
    {
        return $datasets;
    }

    /**
     * @inheritDoc
     */
    public function manipulateDataset(array $dataset): array
    {
        return $dataset;
    }
}
