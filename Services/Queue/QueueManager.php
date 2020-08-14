<?php

namespace Wizzy\Search\Services\Queue;

use Wizzy\Search\Helpers\DB\WizzyTables;
use Wizzy\Search\Model\QueueFactory;
use Wizzy\Search\Model\ResourceModel\Queue;
use Wizzy\Search\Services\DB\ConnectionManager;

class QueueManager {

   private $queueFactory;
   private $queueResourceModel;
   private $connectionManager;

   const JOB_TO_EXECUTE_STATUS = 0;
   const JOB_IN_PROGRESS_STATUS = 1;
   const JOB_PROCESSED_STATUS = 2;
   const JOB_CANCELLED_STATUS = -1;

   public function __construct(QueueFactory $queueFactory, Queue $queueResourceModel, ConnectionManager $connectionManager) {
      $this->queueFactory = $queueFactory;
      $this->queueResourceModel = $queueResourceModel;
      $this->connectionManager = $connectionManager;
   }

   public function enqueue(string $class, $storeId, array $data = []) {
      $queue = $this->queueFactory->create();
      $queue->setData([
         'class' => $class,
         'store_id' => $storeId,
         'data' => json_encode($data),
      ]);
      $this->queueResourceModel->save($queue);
   }

   public function changeStatus(array $jobs, int $status, $errors = NULL) {
      if (count($jobs) > 0) {
         foreach ($jobs as $index => $job) {
            $jobs[$index]['status'] = $status;
            $jobs[$index]['errors'] = $errors;

            // tries only gets updated when status is changed to in progress.
            if ($status != self::JOB_CANCELLED_STATUS) {
               $jobs[$index]['tries'] += 1;
            }
         }
         $this->connectionManager->insertMultiple(WizzyTables::$SYNC_QUEUE_TABLE_NAME, $jobs, TRUE);
      }
   }

   public function dequeue($maxJobs = 5) {
      $jobs = $this->queueFactory->create()->getCollection()
         ->addFieldToFilter('status', self::JOB_TO_EXECUTE_STATUS)
         ->setOrder('id', 'ASC')
         ->setPageSize($maxJobs)
         ->setCurPage(1);
      $jobsData = [];

      foreach ($jobs as $job) {
         $jobsData[] = $job->getData();
      }

      return $jobsData;
   }

   public function clear($storeId, $jobClass = NULL) {
      $jobs = $this->getAllInProgressJobs($storeId, $jobClass);
      $this->changeStatus($jobs, self::JOB_CANCELLED_STATUS);
   }

   private function getAllInProgressJobs($storeId, $jobClass = NULL) {
      $jobs = $this->queueFactory->create()->getCollection()
         ->addFieldToFilter('status', self::JOB_TO_EXECUTE_STATUS)
         ->addFieldToFilter('store_id', $storeId);
      if ($jobClass) {
         $jobs = $jobs->addFieldToFilter('class', $jobClass);
      }
      $jobs = $jobs->setOrder('id', 'ASC');

      $jobsData = [];

      foreach ($jobs as $job) {
         $jobsData[] = $job->getData();
      }

      return $jobsData;
   }
}