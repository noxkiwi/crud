<?php declare(strict_types = 1);
namespace noxkiwi\crud\Helper;

abstract class UrlHelper
{
    public function listUrl(array $filters): string
    {
        return '';
    }
    public function editUrl(array $filters): string
    {
        return '';
    }
}
