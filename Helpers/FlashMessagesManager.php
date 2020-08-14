<?php

namespace Wizzy\Search\Helpers;

use Magento\Framework\Message\ManagerInterface;

class FlashMessagesManager {
  private $messagesManager;
  public function __construct(ManagerInterface $messagesManager) {
    $this->messagesManager = $messagesManager;
  }

  public function success($message) {
    $this->messagesManager->addSuccessMessage(__($message));
  }

  public function error($message) {
    $this->messagesManager->addErrorMessage(__($message));
  }

  public function warning($message) {
    $this->messagesManager->addWarningMessage(__($message));
  }

  public function notice($message) {
    $this->messagesManager->addNoticeMessage(__($message));
  }

}