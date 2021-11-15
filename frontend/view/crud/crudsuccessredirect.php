<?php declare(strict_types = 1);
namespace noxkiwi\core; ?>
<div class="alert alert-success alert-dismissible" role="alert" id="crudAlert">
    <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span
                class="sr-only">Close</span></button>
    Der Eintrag wurde erfolgreich aktualisiert.
    <br/>Wenn Sie nicht automatisch weiter geleitet werden, <a href="<?= Response::getInstance()->get('redirect') ?>">klicken
        Sie hier</a>.
</div>
<script>
    $(document).ready(function () {
        $("html, body").animate({
            scrollTop : $("#crudAlert").offset().top
        });
    });
</script>
