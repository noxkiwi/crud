<?php declare(strict_types = 1);
namespace noxkiwi\crud\Consumer;

use Exception;
use noxkiwi\core\Exception\InvalidArgumentException;
use noxkiwi\core\Traits\LanguageImprovementTrait;
use noxkiwi\crud\Message\BulkDeleteMessage;
use noxkiwi\crud\Message\BulkEditMessage;
use noxkiwi\dataabstraction\Model;
use noxkiwi\queue\Consumer\RabbitmqConsumer;
use noxkiwi\queue\Message;
use function class_exists;
use function count;
use function get_class;
use function microtime;

/**
 * I am the Queue Consumer for all Crud related Messages.
 *
 * @package      noxkiwi\crud
 * @author       Jan Nox <jan@nox.kiwi>
 * @license      https://nox.kiwi/license
 * @copyright    2020 nox.kiwi
 * @version      1.0.0
 * @link         https://nox.kiwi/
 */
final class CrudConsumer extends RabbitmqConsumer
{
    use LanguageImprovementTrait;

    protected const MESSAGE_TYPES = [
        BulkEditMessage::class,
        BulkDeleteMessage::class
    ];

    /**
     * @inheritDoc
     */
    public function process(Message $message): bool
    {
        $this->logDebug(" [*] Message is of type {$this->returnIt(get_class($message))}");
        if ($message instanceof BulkEditMessage) {
            return $this->processBulkEdit($message);
        }
        if ($message instanceof BulkDeleteMessage) {
            return $this->processBulkDelete($message);
        }
        $this->logError(" [*] Unfortunately, I'm not aware of this message type.");

        return true;
    }

    /**
     * I will process the bulk DELETE message of the CRUD queue.
     *
     * @param \noxkiwi\crud\Message\BulkDeleteMessage $message
     *
     * @return bool
     */
    private function processBulkDelete(BulkDeleteMessage $message): bool
    {
        if (! class_exists($message->model)) {
            return false;
        }
        try {
            $model = $message->model::getInstance();
            $model->addFilter($model->getPrimarykey(), $message->primaryKeys);
            $delete = $message->model::getInstance();
        } catch (Exception $exception) {
            $this->logError(' [*] Unfortunately an error occured');
            $this->logError(" [*] - Exception message was {$exception->getMessage()}");

            return false;
        }
        $entries = $model->search()->getEntries();
        $count   = count($entries);
        $index   = 1;
        foreach ($entries as $entry) {
            $primaryKey = $entry->get()[$model->getPrimarykey()];
            $delete->delete($primaryKey);
            $percent = round($index / $count * 100);
            $this->logNotice(" [*] {$percent}%: Removed item {$index} of {$count}.");
            $this->logDebug(" [*] Removed entry #{$primaryKey} from  model {$message->model}.");
            $index++;
        }

        return true;
    }

    /**
     * I will process the bulk EDIT message of the CRUD queue.
     *
     * @param \noxkiwi\crud\Message\BulkEditMessage $message
     *
     * @return bool
     */
    private function processBulkEdit(BulkEditMessage $message): bool
    {
        if (! class_exists($message->model)) {
            return false;
        }
        try {
            $model = $message->model::getInstance();
            $model->addFilter($model->getPrimarykey(), $message->primaryKeys);
        } catch (Exception $exception) {
            $this->logError(' [*] Unfortunately an unpredicted error occured.');
            $this->logError(" [*] - Exception message was {$exception->getMessage()}");

            return false;
        }
        $entries = $model->search()->getEntries();
        $count   = count($entries);
        $index   = 1;
        foreach ($entries as $entry) {
            $percent = round($index / $count * 100);
            foreach ($message->fields as $fieldName => $fieldValue) {
                try {
                    $entry->__set($fieldName, $fieldValue);
                    $this->logDebug(" [*] - Setting field {$fieldName} to {$this->returnIt(print_r($fieldValue, true))}");
                } catch (InvalidArgumentException $exception) {
                    $this->logError(" [*] Invalid value for {$fieldName} encountered. Skipping this entry!");
                    $this->logError(" [*] - Exception message was {$exception->getMessage()}");
                    continue 2;
                }
            }
            try {
                $this->logDebug(" [*] I'm going to save the changes now!");
                $start = microtime(true);
                $entry->save();
            } catch (InvalidArgumentException $exception) {
                $this->logError(' [*] Unfortunately the entry could not be validated.');
                $this->logError(" [*] - Exception message was {$exception->getMessage()}");
                continue;
            } catch (Exception $exception) {
                $this->logError(' [*] Unfortunately an unknown error occured.');
                $this->logError(" [*] - Exception message was {$exception->getMessage()}");
                continue;
            }
            $elapsed = (microtime(true) - $start) * 1000;
            $this->logNotice(" [*] {$percent}%: Saved item {$index} of {$count}.");
            $this->logDebug(" [*] - Saving took {$elapsed}ms to finish.");
            $index++;
        }

        return true;
    }
}

