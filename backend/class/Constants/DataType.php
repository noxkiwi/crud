<?php declare(strict_types = 1);
namespace noxkiwi\crud\Constants;

/**
 * I am an action for the CRUD system.
 * May it be an ENTRY related action, a BULK action or any other kind of action.
 *
 * @package      noxkiwi\crud\Constants
 * @author       Jan Nox <jan@nox.kiwi>
 * @license      https://nox.kiwi/license
 * @copyright    2020 nox.kiwi
 * @version      1.0.0
 * @link         https://nox.kiwi/
 */
final class DataType
{
    public const TEXT           = 'text';
    public const NUMBER         = 'number';
    public const NUMBER_NATURAL = 'number_natural';
    public const DATE           = 'date';
    public const DATE_TIME      = 'date_time_local';
    public const FILE           = 'file';
}
