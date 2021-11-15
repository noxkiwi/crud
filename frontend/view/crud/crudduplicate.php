<?php declare(strict_types = 1);
namespace noxkiwi\core;

use noxkiwi\core\Constants\Mvc;
use noxkiwi\core\Helper\FrontendHelper;
use noxkiwi\core\Helper\LinkHelper;
use noxkiwi\translator\Translator;

?>
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header">
                <div class="title"><?= Translator::get('DUPLICATE.CONFIRM') ?></div>
            </div>
            <div class="box-content padded">
                <div class="alert alert-warning alert-dismissible" role="alert">
                    <?= Translator::get('DUPLICATE.REALLY') ?>
                </div>
                <a class="btn btn-danger" href="<?= LinkHelper::makeUrl([Mvc::VIEW => 'crudlist']) ?>"><?= FrontendHelper::icon('chevron-left') ?> <?= Translator::get(
                        'BTN.ABORT'
                    ) ?></a>
                <a class="btn btn-success pull-right" id="btnConfirm"
                   href="<?= LinkHelper::makeUrl(
                       [
                           Response::getInstance()->get('keyname') => Response::getInstance()->get(
                               'keyvalue'
                           ),
                           Crud::PARAM_CONFIRM                         => 1
                       ]
                   ) ?>"> <?= Translator::get(
                        'BTN.DUPLICATE'
                    ) ?> <?= FrontendHelper::icon('copy') ?></a>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header">
                <div class="title"><?= Translator::get('DUPLICATE.INFO') ?></div>
            </div>
            <div class="box-contend padded">
                <?= Response::getInstance()->get('form') ?>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).on("ready", function () {
        $("#btnConfirm").focus();
    });
</script>
