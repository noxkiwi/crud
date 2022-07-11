<?php declare(strict_types = 1);
namespace noxkiwi\crud\Interfaces;

use noxkiwi\crud\Frontend\Cell;

/**
 * I am the interface for all Manipulators.
 *
 * @package      noxkiwi\crud
 * @author       Jan Nox <jan.nox@pm.me>
 * @license      https://nox.kiwi/license
 * @copyright    2020 nox.kiwi
 * @version      1.0.0
 * @link         https://nox.kiwi/
 */
interface ManipulatorInterface
{
    /**
     * @param array $responseRow
     * @param array $dataset
     *
     * @return array
     */
    public function manipulateRow(array $responseRow, array $dataset): array;

    /**
     * I will manipulate the values for the given $fieldName.
     *
     * @param string $fieldName
     * @param array  $dataset
     *
     * @return \noxkiwi\crud\Frontend\Cell
     */
    public function manipulateField(string $fieldName, array $dataset): Cell;

    /**
     * @param array $datasets
     *
     * @return array
     */
    public function manipulateDatasets(array $datasets): array;

    /**
     * @param array $dataset
     *
     * @return array
     */
    public function manipulateDataset(array $dataset): array;
}
