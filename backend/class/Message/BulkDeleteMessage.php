<?php declare(strict_types = 1);
namespace noxkiwi\crud\Message;

use noxkiwi\queue\Message;

/**
 * I am the message that will utilize Crud to delete a bulk of entries.
 *
 * @package      noxkiwi\crud
 * @author       Jan Nox <jan@nox.kiwi>
 * @license      https://nox.kiwi/license
 * @copyright    2020 nox.kiwi
 * @version      1.0.0
 * @link         https://nox.kiwi/
 */
class BulkDeleteMessage extends Message
{
    /**
     * @var string
     */
    public string $model;
    /**
     * @var int[]
     */
    public array $primaryKeys;
}

