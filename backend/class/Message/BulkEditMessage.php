<?php declare(strict_types = 1);
namespace noxkiwi\crud\Message;

use noxkiwi\queue\Message;

/**
 * I am the Queue Message that will overwrite all $primaryKeys in $model with the given $fields.
 *
 * @package      noxkiwi\crud\Message
 * @author       Jan Nox <jan.nox@pm.me>
 * @license      https://nox.kiwi/license
 * @copyright    2020 nox.kiwi
 * @version      1.0.0
 * @link         https://nox.kiwi/
 */
class BulkEditMessage extends Message
{
    /**
     * @var string
     */
    public string $model;
    /**
     * @var int[]
     */
    public array $primaryKeys;
    /**
     * @var array[]
     */
    public array $fields;
}

