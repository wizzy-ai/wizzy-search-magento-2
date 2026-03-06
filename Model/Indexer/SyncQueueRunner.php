<?php
namespace Wizzy\Search\Model\Indexer;

use Wizzy\Search\Model\API\Response;
use Wizzy\Search\Services\Indexer\IndexerOutput;
use Wizzy\Search\Services\Queue\QueueManager;
use Wizzy\Search\Services\Store\StoreAdvancedConfig;
use Magento;

class SyncQueueRunner implements Magento\Framework\Indexer\ActionInterface, Magento\Framework\Mview\ActionInterface
{

    private $queueManager;
    private $storeAdvancedConfig;
    private $maxQueueJobsToExecute;
    private $output;

    private const MAX_RETRIES = 3;

    public function __construct(
        QueueManager $queueManager,
        IndexerOutput $output,
        StoreAdvancedConfig $storeAdvancedConfig
    ) {
        $this->queueManager = $queueManager;
        $this->output = $output;
        $this->storeAdvancedConfig = $storeAdvancedConfig;

      // This needs to be moved into module settings.
        $this->maxQueueJobsToExecute = 7;
    }

  /*
   * No need to execute anything.
   */
    public function execute($ids)
    {
        return $this;
    }

  /*
   * Execute Queue Jobs
   */
    public function executeFull()
    {
        $this->maxQueueJobsToExecute = $this->storeAdvancedConfig->getSyncDequeueSize();
        $jobs = $this->queueManager->dequeue($this->maxQueueJobsToExecute);
        $this->output->writeDiv();
        $this->output->writeln(__('Started Queue Processing'));

        if (empty($jobs)) {
            $this->output->writeln(__('No processors in queue'));
            return $this;
        }
        $this->queueManager->changeStatus($jobs, QueueManager::JOB_IN_PROGRESS_STATUS);

        foreach ($jobs as $jobData) {
            $jobClass = $jobData['class'];
            $storeId = $jobData['store_id'];
            $data = json_decode($jobData['data'], true);
            $this->output->writeln(__('Started executing Processor #' . $jobData['id']));
            $jobData['started_processing_at'] = date('Y-m-d H:i:s');

            if (class_exists($jobClass)) {
                try {
                    $job = Magento\Framework\App\ObjectManager::getInstance()->get($jobClass);
                    $jobResponse = $job->execute($data, $storeId);
                    if ($jobResponse === QueueManager::JOB_EXECUTION_BACK_IN_QUEUE) {
                        $this->output->writeln(__('Processor #' . $jobData['id'] . ' added back in queue.'));
                        $this->queueManager->changeStatus([$jobData], QueueManager::JOB_TO_EXECUTE_STATUS);
                    } elseif ($jobResponse === true) {
                        $this->output->writeln(__('Processor #' . $jobData['id'] . ' executed successfully.'));
                        $jobData['complete_processing_at'] = date('Y-m-d H:i:s');
                        $this->queueManager->changeStatus([$jobData], QueueManager::JOB_PROCESSED_STATUS);
                    } else {
                        $errorMessage = $this->getQueueError($jobResponse);

                        $this->output->log([
                           'message' => __('Processor #' . $jobData['id'] . ' failed.'),
                           'Processor Class' => $jobClass,
                           'Store ID' => $storeId,
                           'Error' => $errorMessage,
                        ]);

                        $this->handleJobFailure($jobData, $errorMessage);
                    }
                } catch (\Exception $exception) {
                    $this->output->log([
                      'Message'  => $exception->getMessage(),
                      'Queue ID' => $jobData['id'],
                      'Processor Class' => $jobClass,
                      'Store ID' => $storeId,
                      'Class' => get_class($exception),
                      'File' => $exception->getFile(),
                      'Line' => $exception->getLine(),
                      'Trace' => $exception->getTraceAsString(),
                    ]);

                    $this->handleJobFailure($jobData, $exception->getMessage());
                }
            } else {
                $this->output->log([
                  'Message'  => __('Processor Class not found'),
                  'Queue ID' => $jobData['id'],
                  'Processor Class' => $jobClass,
                  'Store ID' => $storeId,
                ]);
            }
            $this->output->writeDiv();
        }

        return $this;
    }

    /**
     * On failure: if tries (current attempt) >= maxRetries, mark job as FAILED so the queue can
     * process other jobs. Otherwise put it back to TO_EXECUTE for retry.
     * Note: tries in DB was already incremented when status was set to IN_PROGRESS.
     */
    private function handleJobFailure(array $jobData, string $errorMessage)
    {
        $currentTries = (int) ($jobData['tries'] ?? 0) + 1;
        if ($currentTries >= self::MAX_RETRIES) {
            $this->output->writeln(__(
                'Processor #%1 marked as Failed after %2 tries. Error: %3',
                $jobData['id'],
                $currentTries,
                $errorMessage
            ));
            $this->queueManager->changeStatus(
                [$jobData],
                QueueManager::JOB_FAILED_STATUS,
                $errorMessage
            );
        } else {
            $this->queueManager->changeStatus(
                [$jobData],
                QueueManager::JOB_TO_EXECUTE_STATUS,
                $errorMessage
            );
        }
    }

    private function getQueueError($jobResponse)
    {
        $errorToSave = "";
        if ($jobResponse instanceof Response) {
            $errorToSave = json_encode($jobResponse->getPayload());
        } elseif (is_array($jobResponse)) {
            $errorToSave = json_encode($jobResponse);
        } elseif (is_string($jobResponse)) {
            $errorToSave = $jobResponse;
        }
        return $errorToSave;
    }

  /*
   * No need to execute anything on list level.
   */
    public function executeList(array $ids)
    {
        return $this;
    }

  /*
   * No need to execute anything on row level.
   */
    public function executeRow($id)
    {
        return $this;
    }
}
