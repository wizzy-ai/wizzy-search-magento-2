<?php

namespace Wizzy\Search\Services\Queue;

use Wizzy\Search\Helpers\DB\WizzyTables;
use Wizzy\Search\Model\QueueFactory;
use Wizzy\Search\Model\ResourceModel\Queue;
use Wizzy\Search\Services\DB\ConnectionManager;

class QueueManager
{

    private $queueFactory;
    private $queueResourceModel;
    private $connectionManager;

    const JOB_TO_EXECUTE_STATUS = 0;
    const JOB_IN_PROGRESS_STATUS = 1;
    const JOB_PROCESSED_STATUS = 2;
    const JOB_CANCELLED_STATUS = -1;

    public function __construct(
        QueueFactory $queueFactory,
        Queue $queueResourceModel,
        ConnectionManager $connectionManager
    ) {
        $this->queueFactory = $queueFactory;
        $this->queueResourceModel = $queueResourceModel;
        $this->connectionManager = $connectionManager;
    }

    public function get($queueId)
    {
        $jobs = $this->queueFactory->create()->getCollection()
          ->addFieldToFilter('id', $queueId);

        $jobsData = null;
        foreach ($jobs as $job) {
            $jobsData = $job->getData();
        }

        return $jobsData;
    }

    public function getLatestInQueueByClass(string $class, $storeId)
    {
        $jobs = $this->queueFactory->create()->getCollection()
          ->addFieldToFilter('class', $class)
          ->addFieldToFilter('store_id', $storeId)
          ->addFieldToFilter('status', self::JOB_TO_EXECUTE_STATUS)
          ->setOrder('id', 'desc')
          ->setPageSize(1);

        $jobData = null;
        foreach ($jobs as $job) {
            $jobData = $job->getData();
        }

        return $jobData;
    }

    public function enqueue(string $class, $storeId, array $data = [])
    {
        $queue = $this->queueFactory->create();
        $queue->setData([
         'class' => $class,
         'store_id' => $storeId,
         'data' => json_encode($data),
        ]);
        $this->queueResourceModel->save($queue);
    }

    public function changeStatus(array $jobs, int $status, $errors = null)
    {
        if (count($jobs) > 0) {
            foreach ($jobs as $index => $job) {
                $jobs[$index]['status'] = $status;
                $jobs[$index]['errors'] = $errors;

                // tries only gets updated when status is changed to in progress.
                if ($status != self::JOB_CANCELLED_STATUS) {
                    $jobs[$index]['tries'] += 1;
                }
            }
            $this->connectionManager->insertMultiple(WizzyTables::$SYNC_QUEUE_TABLE_NAME, $jobs, true);
        }
    }

    public function edit(array $job)
    {
         $this->connectionManager->insertMultiple(WizzyTables::$SYNC_QUEUE_TABLE_NAME, [$job], true);
    }

    public function dequeue($maxJobs = 7)
    {
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

    public function clear($storeId, $jobClass = null)
    {
        $jobs = $this->getAllInProgressJobs($storeId, $jobClass);
        $this->changeStatus($jobs, self::JOB_CANCELLED_STATUS);
    }

    public function clearAll()
    {
        $jobs = $this->getAllClearableJobs();
        $this->changeStatus($jobs, self::JOB_CANCELLED_STATUS);

        return $jobs;
    }

    public function truncateAllCompletedOrCancelled()
    {
        $jobs = $this->queueFactory->create()->getCollection()
          ->addFieldToFilter('status', ["in" => [self::JOB_PROCESSED_STATUS, self::JOB_CANCELLED_STATUS]]);
        $jobs = $jobs->setOrder('id', 'ASC');
        $jobs->walk('delete');

        return $jobs;
    }

    private function getAllClearableJobs($status = [self::JOB_TO_EXECUTE_STATUS, self::JOB_IN_PROGRESS_STATUS])
    {
        $jobs = $this->queueFactory->create()->getCollection()
          ->addFieldToFilter('status', ["in" => $status]);
        $jobs = $jobs->setOrder('id', 'ASC');

        $jobsData = [];
        foreach ($jobs as $job) {
            $jobsData[] = $job->getData();
        }

        return $jobsData;
    }

    public function enqueueAllInProgress($status = [self::JOB_TO_EXECUTE_STATUS, self::JOB_IN_PROGRESS_STATUS])
    {
        $jobs = $this->getAllClearableJobs($status);
        $this->changeStatus($jobs, self::JOB_TO_EXECUTE_STATUS);
        return $jobs;
    }

    private function getAllInProgressJobs($storeId, $jobClass = null)
    {
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
