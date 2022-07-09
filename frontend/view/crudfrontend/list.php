<?php declare(strict_types = 1);
namespace noxkiwi\core;

use noxkiwi\core\Constants\Mvc;
use noxkiwi\core\Helper\JsonHelper;
use noxkiwi\core\Helper\LinkHelper;
use noxkiwi\crud\Action;
use noxkiwi\frontend\Element\Icon;
use noxkiwi\translator\Translator;

$crudId            = '';
$modelName         = Request::getInstance()->get('modelName');
$constructorObject = Response::getInstance()->get('config');
// Build header
$thead = '';
foreach ($constructorObject['columns'] as $column) {
    $Translatord = Translator::get("{$column['name']}_LABEL");
    $thead       .= chr(10) . "<th class=\"text-center\">{$Translatord}</th>";
}
// TRANSLATIONS
$title = Translator::get("CRUD.{$modelName}_LIST");
/** @var \noxkiwi\crud\Action[] $actionsBulk */
$actionsBulk = [];
/** @var \noxkiwi\crud\Action[] $actionsBulk */
$actionsTop = [];
// FAKE ACTION
$createAction = new Action();
$createAction->add(Icon::getFromName('CREATE'));
$createAction->add(Translator::get('crudcreate'));
$createAction->setTarget(Action::TARGET_MODAL);
$createAction->setHref(LinkHelper::get([Mvc::CONTEXT => 'crud', Mvc::VIEW => 'create', 'modelName' => $modelName]));
$actionsTop[] = $createAction;
$topActions   = '';
foreach ($actionsTop as $value) {
    $topActions .= $value->render();
}
$bulk = '';
if (! empty($actionsBulk)) {
    $bulkActions = '';
    foreach ($actionsBulk as $key => $value) {
        $bulkActions .= $value->render();
    }
    $bulk = <<<HTML
<div class="btn-group pull-right">
    <button class="btn btn-success dropdown-toggle" data-toggle="dropdown">Bulk actions <span class="caret"></span></button>
    <ul class="dropdown-menu">
        {$bulkActions}
    </ul>
</div>
HTML;
}
$top = <<<HTML
<span class="float-right">
    $topActions
    $bulk
</span>
HTML;
// BUILD CARD
$card  = <<<HTML
<div class="card">
    <div role="heading" class="card-header">
        $title
        $top
    </div>
    <div role="list" class="card-body">
        <table id="crudList$crudId" class="table table-xs table-striped table-bordered compact nkCrud" data-crudId="$crudId">
            <thead>
                <tr>
                    $thead
                </tr>
            </thead>
        </table>
    </div>
</div>
HTML;
$modal = <<<HTML
<div id="crudModal$crudId" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modal title</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Modal body text goes here.</p>
            </div>
            <div class="modal-footer float-left">
            	<div>
                    <button type="button" class="btn btn-warning" data-bs-dismiss="modal">Close</button>
            	</div>
            	<div class="modal-foot-container">
            	</div>
            </div>
        </div>
    </div>
</div>
HTML;
// SCRIPT
$json       = JsonHelper::encode($constructorObject);
$coreScript = LinkHelper::get([Mvc::CONTEXT => 'resource', Mvc::VIEW => 'file', 'file' => 'js/Core']);# /?context=resource&file=js%2FCore
$crudScript = LinkHelper::get([Mvc::CONTEXT => 'resource', Mvc::VIEW => 'file', 'file' => 'js/Crud']);# /?context=resource&file=js%2FCore
$script     = <<<HTML
<script>var list;</script>
<script src="$coreScript"></script>
<script type="module">
    import Crud from '/$crudScript';
    let crud = new Crud('$crudId', $json);
</script>
HTML;
echo "$card $modal $script";
