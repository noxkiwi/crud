<?php declare(strict_types = 1);
namespace noxkiwi\crud;

use noxkiwi\core\Config;
use noxkiwi\core\Constants\Mvc;
use noxkiwi\core\Helper\LinkHelper;
use noxkiwi\crud\Interfaces\ManipulatorInterface;
use noxkiwi\dataabstraction\Model;
use noxkiwi\frontend\Element\Badge;
use noxkiwi\frontend\Element\Icon;
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

    public function __construct(Model $model, Config $config)
    {
        $this->config = $config;
        $this->model  = $model;
    }

    final protected function getConfig(): Config
    {
        return $this->config;
    }

    final protected function getModel(): Model
    {
        return $this->model;
    }

    public function manipulateRow(array $responseRow, array $dataset): array
    {
        foreach ($this->getModel()->getFieldNames() as $fieldName) {
            $responseRow[$fieldName] = $this->manipulateField($fieldName, $dataset);
        }
        $responseRow[Crud::COLUMN_ACTION] = $this->buildActions($responseRow, $dataset);

        return $responseRow;
    }

    protected function buildAction(array $action, array $responseRow, array $dataset): HtmlTag
    {
        $action['link']['modelName']                        = $this->getModel()->getModelName();
        $action['link']['identifier']                       = $dataset[$this->getModel()->getPrimarykey()];
        $action['link'][$this->getModel()->getPrimarykey()] = $dataset[$this->getModel()->getPrimarykey()];
        $url                                                = LinkHelper::get($action['link']);
        $editButton                                         = new Action();
        $editButton->setTarget($action['target'] ?? Action::TARGET_TOP);
        $editButton->setHref($url);
        $editButton->add(Icon::getFromName($action['icon']));

        return $editButton;
    }

    /**
     * I will [To be filled by Jan]
     *
     * @param array $responseRow
     * @param array $dataset
     *
     * @return string[]
     */
    protected function buildActions(array $responseRow, array $dataset): array
    {
        $actions = $this->getConfig()->get('action>element', []);
        $content = '';
        foreach ($actions as $action) {
            $content .= $this->buildAction($action, $responseRow, $dataset);
        }

        return [
            'sort'    => $content,
            'display' => $content,
            'filter'  => $content
        ];
    }

    private function manipulatePrimary(string $fieldName, array $dataset): array
    {
        return [
            'sort'    => $dataset[$fieldName] ?? '',
            'display' => $dataset[$fieldName] ?? '',
            'filter'  => 'primary_' . $dataset[$fieldName] . '_' ?? '',
        ];
    }

    private array $foundManipulators;

    private function manipulatorFound(string $fieldName): bool
    {
        if (! isset($this->foundManipulators[$fieldName])) {
            $this->foundManipulators[$fieldName] = method_exists($this, "manipulate{$fieldName}");
        }

        return $this->foundManipulators[$fieldName];
    }

    /**
     * @param string $fieldName
     * @param array  $dataset
     *
     * @return string[]
     */
    public function manipulateField(string $fieldName, array $dataset): array
    {
        if ($this->manipulatorFound($fieldName)) {
            return $this->{"manipulate{$fieldName}"}($fieldName, $dataset);
        }
        if ($this->getModel()->getPrimarykey() === $fieldName) {
            return $this->manipulatePrimary($fieldName, $dataset);
        }

        return [
            'sort'    => $dataset[$fieldName] ?? '',
            'display' => $dataset[$fieldName] ?? '',
            'filter'  => $dataset[$fieldName] ?? '',
        ];
    }

    /**
     * @param string                         $fieldName
     * @param array                          $dataset
     * @param \noxkiwi\dataabstraction\Model $model
     * @param string                         $displayField
     *
     * @return string[]
     */
    private function manipulateRemoteList(string $fieldName, array $dataset, Model $model, string $displayField): array
    {
        $primaryKeys        = explode(',', trim((string)($dataset[$fieldName] ?? '')));
        $display            = '';
        $filter             = '';
        $modelClassElements = explode('\\', $model::class);
        $modelName          = str_replace('Model', '', end($modelClassElements));
        $table              = $model::TABLE;
        $translator         = Translator::getInstance();
        foreach ($primaryKeys as $remoteId) {
            if (empty($remoteId)) {
                continue;
            }
            $remoteId = trim((string)$remoteId);
            $entry    = $model->loadEntry($remoteId);
            $badge    = new Badge();
            if ($entry === null) {
                $url = '#';
                $badge->addClass('bg-danger');
                $badge->add("[MISSING $table] - $remoteId");
            } else {
                $badge->add("$remoteId: {$entry->{$displayField}}");
                $url     = LinkHelper::get([
                                               Mvc::CONTEXT => 'crudfrontend',
                                               Mvc::VIEW    => 'list',
                                               'modelName'  => $modelName,
                                               'q'          => "primary_{$remoteId}_"
                                           ]);
                $content = $translator->translate((string)$entry->{$displayField});
                $filter  .= "F:$content";
            }
            $display .= <<<HTML
<a href="$url">$badge</a>
HTML;
        }

        return ['sort' => '', 'display' => $display, 'filter' => $filter];
    }

    /**
     * @param string                         $fieldName
     * @param array                          $dataset
     * @param \noxkiwi\dataabstraction\Model $model
     * @param string                         $displayField
     *
     * @return string[]
     */
    private function manipulateRemote(string $fieldName, array $dataset, Model $model, string $displayField): array
    {
        $fieldId    = [$dataset[$fieldName] ?? ''];
        $display    = '';
        $filter     = '';
        $a          = explode('\\', get_class($model));
        $modelClass = str_replace('Model', '', end($a));
        foreach ($fieldId as $fieldId) {
            $fieldId = trim((string)$fieldId);
            $field   = $model->loadEntry($fieldId);
            if ($field === null) {
                continue;
            }
            $badge = new Badge();
            $badge->add("{$fieldId}: {$field->{$displayField}}");
            $url     = LinkHelper::get([
                                           Mvc::CONTEXT => 'crudfrontend',
                                           Mvc::VIEW    => 'list',
                                           'modelName'  => $modelClass,
                                           'q'          => "primary_{$fieldId}_"
                                       ]);
            $display .= <<<HTML
<a href="$url">
    $badge
</a>
HTML;
            $filter  .= "F:{$field->{$displayField}}";
        }

        return ['sort' => '', 'display' => $display, 'filter' => $filter];
    }

    private function manipulateFlagField(string $fieldName, array $dataset, Model $model): array
    {
        $display    = '';
        $filter     = '';
        $line       = '';
        $a          = explode('\\', get_class($model));
        $modelClass = str_replace('Model', '', end($a));
        foreach ($model->getConfig()->get('flags', []) as $flagId => $flagValue) {
            if (($dataset[$fieldName] & $flagId) === $flagId) {
                $badge = new Badge();
                $badge->setTitle("{$this->translate("{$modelClass}_FLAGS_{$flagValue}_DESCRIPTION")}");
                $badge->add("{$flagId}: {$flagValue}");
                $display .= <<<HTML
$line $badge
HTML;
                $filter  .= " {$fieldName}";
                $line    = '<br />';
            }
        }

        return ['sort' => '', 'display' => $display, 'filter' => $filter];
    }

    private function manipulateTranslatedField(string $fieldName, array $dataset): array
    {
        $translator  = Translator::getInstance();
        $key         = $dataset[$fieldName];
        $translation = $translator->translate($key);
        $badge       = new Badge();
        $url         = LinkHelper::get([
                                           Mvc::CONTEXT => 'crudfrontend',
                                           Mvc::VIEW    => 'list',
                                           'modelName'  => 'language',
                                           'q'          => "primary_{$key}_"
                                       ]);
        $target      = '';
        if ($translation === Translator::normalizeKey($key)) {
            $url    = "?tool=translation&name=$key";
            $target = ' target="blank"';
            $badge->setClass($badge->getClassString() . 'bg-danger');
            $badge->add(' [NOT TRANSLATED]  -  ');
        }
        $badge->add($translation);
        $display = <<<HTML
<a href="$url" $target>$badge</a><br />
HTML;

        return ['sort' => '', 'display' => $display, 'filter' => $key];
    }

    public function manipulateDatasets(array $datasets): array
    {
        return $datasets;
    }

    public function manipulateDataset(array $dataset): array
    {
        return $dataset;
    }
}
