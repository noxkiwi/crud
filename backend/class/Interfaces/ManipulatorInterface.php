<?php declare(strict_types = 1);
namespace noxkiwi\crud\Interfaces;

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
    public function manipulateRow(array $responseRow, array $dataset): array;

    public function manipulateField(string $fieldName, array $dataset);

    public function manipulateDatasets(array $datasets): array;

    public function manipulateDataset(array $dataset): array;
}
