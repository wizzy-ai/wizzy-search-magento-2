<?php
namespace Wizzy\Search\Model\Indexer;

use Wizzy\Search\Services\Queue\QueueManager;
use Magento;

class SyncQueueRunner implements Magento\Framework\Indexer\ActionInterface, Magento\Framework\Mview\ActionInterface {

  private $queueManager;
  private $maxQueueJobsToExecute;

  public function __construct(QueueManager $queueManager) {
    $this->queueManager = $queueManager;

    // This needs to be moved into module settings.
    $this->maxQueueJobsToExecute = 5;
  }

  /*
   * No need to execute anything.
   */
  public function execute($ids){
    return $this;
  }

  /*
   * Execute Queue Jobs
   */
  public function executeFull(){
    $jobs = $this->queueManager->dequeue($this->maxQueueJobsToExecute);
    if (empty($jobs)) {
      return $this;
    }
    $this->queueManager->changeStatus($jobs, QueueManager::JOB_IN_PROGRESS_STATUS);

    foreach ($jobs as $jobData) {
      $jobClass = $jobData['class'];
      $storeId = $jobData['store_id'];
      $data = json_decode($jobData['data'], TRUE);

      if (class_exists($jobClass)) {
        try {
          $job = Magento\Framework\App\ObjectManager::getInstance()->get($jobClass);
          $jobResponse = $job->execute($data, $storeId);
          if ($jobResponse) {
             $this->queueManager->changeStatus([$jobData], QueueManager::JOB_PROCESSED_STATUS);
          }
          else {
             $this->queueManager->changeStatus([$jobData], QueueManager::JOB_TO_EXECUTE_STATUS, '');
          }
        }
        catch (\Exception $exception) {
          // Log this exception for devs.
          $this->queueManager->changeStatus([$jobData], QueueManager::JOB_TO_EXECUTE_STATUS, $exception->getMessage());
        }
      }
    }

    return $this;
  }

  /*
   * No need to execute anything on list level.
   */
  public function executeList(array $ids){
    return $this;
  }

  /*
   * No need to execute anything on row level.
   */
  public function executeRow($id){
    return $this;
  }

}