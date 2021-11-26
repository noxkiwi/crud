<?php declare(strict_types = 1);
namespace noxkiwi\crud;

use noxkiwi\core\Path as BasePath;

/**
 * I am the Path collection for the crud project.
 *
 * @package      noxkiwi\crud
 * @author       Jan Nox <jan.nox@pm.me>
 * @license      https://nox.kiwi/license
 * @copyright    2021 nox.kiwi
 * @version      1.0.1
 * @link         https://nox.kiwi/
 */
final class Path extends BasePath
{
    public const CONFIG_CRUD = self::CONFIG_DIR . 'crud/';
}
