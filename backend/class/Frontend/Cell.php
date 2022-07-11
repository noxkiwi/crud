<?php declare(strict_types = 1);
namespace noxkiwi\crud\Frontend;

/**
 * I am a Cell shown in the CRUD editor.
 *
 * @package      noxkiwi\crud\Frontend
 * @author       Jan Nox <jan.nox@pm.me>
 * @license      https://nox.kiwi/license
 * @copyright    2022 nox.kiwi
 * @version      1.0.0
 * @link         https://nox.kiwi/
 */
final class Cell
{
    /** @var mixed I am the DataTable render option for sorting the rows. */
    public mixed $sort;
    /** @var string I am the DataTable render option for the actual display. */
    public string $display;
    /** @var mixed I am the DataTable render option for filtering. */
    public mixed $filter;
    /** @var mixed I am the DataTable render option for exports. */
    public mixed $export;
}
