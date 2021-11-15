<?php declare(strict_types = 1);
namespace noxkiwi\crud;

use noxkiwi\hook\Hook as BaseHook;

/**
 * I am the collection of hooks for the CRUD System.
 *
 * @package      noxkiwi\crud
 * @author       Jan Nox <jan@nox.kiwi>
 * @license      https://nox.kiwi/license
 * @copyright    2020 - 2021 nox.kiwi
 * @version      1.0.1
 * @link         https://nox.kiwi/
 */
final class Hook extends BaseHook
{
    public const CRUD_CREATE_FORM_INIT        = 'initForm';
    public const CRUD_CREATE_BEFORE_SAVE      = 'beforeSave';
    public const CRUD_CREATE_AFTER_VALIDATION = 'saveValid';
    public const CRUD_CREATE_SUCCESS          = 'saveSuccess';
}
