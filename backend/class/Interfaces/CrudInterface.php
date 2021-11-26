<?php declare(strict_types = 1);
namespace noxkiwi\crud\Interfaces;

use noxkiwi\crud\Crud;
use noxkiwi\dataabstraction\Model;
use noxkiwi\formbuilder\Form;

/**
 * I am the interface of the CRUD classes.
 *
 * @package      noxkiwi\crud\Interfaces
 * @author       Jan Nox <jan.nox@pm.me>
 * @license      https://nox.kiwi/license
 * @copyright    2020 nox.kiwi
 * @version      1.0.0
 * @link         https://nox.kiwi/
 */
interface CrudInterface
{
    /**
     * Returns the private model of this instance
     *
     * @return       \noxkiwi\dataabstraction\Model
     */
    public function getModel(): Model;

    /**
     * Returns a Form HTML code for creating an object. After validating the data in the Form class AND validating the
     * data in the model class, we will try to store the data in the model.
     */
    public function create();

    /**
     * I will generate a form, either on the previously set fields that shall be editable,
     * or based on all the fields the model has.
     *
     * @param int|string|null $primaryKey
     * @param bool       $addSubmitButton
     *
     * @return \noxkiwi\formbuilder\Form
     */
    public function buildForm(int|string|null $primaryKey = null, bool $addSubmitButton = null): Form;

    /**
     * Loads one object from the CRUD generator's model if the primary key is defined.
     *
     * @param mixed $primaryKey
     *
     * @return       \noxkiwi\crud\Crud
     */
    public function useEntry($primaryKey = null): Crud;

    /**
     */
    public function bulkDelete(): void;

    /**
     * Returnes the Form HTML code for editing an existing entry. Will make sure the given data is compliant to the
     * Form's and model's configuration
     *
     * @param mixed $primaryKey
     *
     */
    public function edit(string|int$primaryKey): void;

    /**
     * @param mixed $primaryKey
     */
    public function delete(string|int $primaryKey): void;

    /**
     * Returns the Form HTML code for showing an existing entry without editing function. Will make sure the given data
     * is compliant to the Form's and model's configuration
     *
     * @param mixed $primaryKey
     */
    public function show(string|int $primaryKey): void;

    /**
     * I will create the form with all data from the element identified by the given $primaryKey.
     * But I will remove the primary key from it to force CRUD to create a new entry with the same data.
     *
     * @param mixed $primaryKey
     */
    public function duplicate($primaryKey): void;
}

