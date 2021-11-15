<?php declare(strict_types = 1);
namespace noxkiwi\core;

use noxkiwi\translator\Translator;
?>
<div class="rsCrudFeedback alert alert-success alert-dismissible" role="alert" id="crudAlert">
    <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span
                class="sr-only">Close</span></button>
    <?= Translator::get(
        'CRUD.' . Response::getInstance()->get('CRUD_FEEDBACK')
    ) ?>
</div>
<script>
    $(document).ready(function () {
        $("html, body").animate({
            scrollTop : $("body").offset().top
        });
        $(".joCrudFeedback").remove();
    });
</script>
