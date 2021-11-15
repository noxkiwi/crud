<?php declare(strict_types = 1);
namespace noxkiwi\core;

use noxkiwi\translator\Translator;
?>
<div class="alert alert-warning alert-dismissible" role="alert" id="crudAlert">
    <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span
                class="sr-only">Close</span></button>
    <?= Translator::get('CRUD.SAVE_ERROR') ?>
</div>
<script>
    $(document).ready(function () {
        $("html, body").animate({
            scrollTop : $("body").offset().top
        });
        <?php foreach (Response::getInstance()->get('errors') as $error) { ?>
        $("#<?=$error['__IDENTIFIER']?>").addClass("MFormError");
        $("#<?=$error['__IDENTIFIER']?>error").html('<?php echo isset($error['__DETAILS'][0]['__CODE']) ? Translator::get(
            $error['__DETAILS'][0]['__CODE']
        ) : Translator::get(
            $error['__CODE']
        )?>');
        <?php } ?>
    });
</script>
