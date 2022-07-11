<?php declare(strict_types = 1);
namespace noxkiwi\crud\Helper;

/**
 * I am the URL Helper for the CRUD system.
 *
 * @package      noxkiwi\crud\Frontend
 * @author       Jan Nox <jan.nox@pm.me>
 * @license      https://nox.kiwi/license
 * @copyright    2022 nox.kiwi
 * @version      1.0.0
 * @link         https://nox.kiwi/
 */
abstract class UrlHelper
{
    /**
     * @param array $filters
     *
     * @return string
     */
    public function listUrl(array $filters): string
    {
        return '';
    }

    /**
     * @param array $filters
     *
     * @return string
     */
    public function editUrl(array $filters): string
    {
        return '';
    }
}
