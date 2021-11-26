<?php declare(strict_types = 1);
namespace noxkiwi\crud\Constants;

/**
 * I am an action for the CRUD system.
 * May it be an ENTRY related action, a BULK action or any other kind of action.
 *
 * @package      noxkiwi\crud\Constants
 * @author       Jan Nox <jan.nox@pm.me>
 * @license      https://nox.kiwi/license
 * @copyright    2020 nox.kiwi
 * @version      1.0.0
 * @link         https://nox.kiwi/
 */
final class DisplayType
{
    public const DEFAULT         = 'input';
    public const NUMBER          = 'number';
    public const DATE            = 'date';
    public const DATE_TIME       = 'date_time_local';
    public const SELECT          = 'select';
    public const SELECT_MULTIPLE = 'select_multiple';
    public const HIDDEN          = 'hidden';
    public const TEXTAREA        = 'textarea';
    public const FILE            = 'file';
    public const ALL             = [
        self::DEFAULT,
        self::NUMBER,
        self::DATE,
        self::DATE_TIME,
        self::SELECT,
        self::SELECT_MULTIPLE,
        self::HIDDEN,
        self::TEXTAREA,
        self::FILE,
    ];
}
