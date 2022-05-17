<?php declare(strict_types = 1);
namespace noxkiwi\crud\Helper;

/**
 *
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
