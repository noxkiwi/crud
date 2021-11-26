<?php declare(strict_types = 1);
namespace noxkiwi\crud;

use noxkiwi\frontend\Tag\Html\Anchor;
use function var_dump;

/**
 * I am an action for the CRUD system.
 * May it be an ENTRY related action, a BULK action or any other kind of action.
 *
 * @package      noxkiwi\crud
 * @author       Jan Nox <jan.nox@pm.me>
 * @license      https://nox.kiwi/license
 * @copyright    2020 nox.kiwi
 * @version      1.0.0
 * @link         https://nox.kiwi/
 */
final class Action extends Anchor
{
    /**
     * I will
     *
     * @param null $data
     *
     * @return string
     */
    public function render($data = null): string
    {
        parent::render($data);
        $click = '';
        if ($this->getTarget() === self::TARGET_MODAL) {
            $this->setTarget('modal');
            $this->setOnClick("Core.ajaxModal('{$this->getHref()}', {}, 'crudModal');");
            $this->setHref('');
        } else {
            $click = <<<TXT
href="{$this->getHref()}"
TXT;
        }
        $content = parent::renderInner();

        return <<<HTML
<a  target="#{$this->getTarget()}"
    title="{$this->getTitle()}"
    style="cursor:pointer"
    onclick="{$this->getOnClick()}"
    $click>
    $content
</a>
HTML;
    }
}
