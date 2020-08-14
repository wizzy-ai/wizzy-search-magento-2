<?php

namespace Wizzy\Search\Model\API;

use Magento\Framework\DataObject;

class Response extends DataObject {
  private static $RESPONSE_STATUS_KEY  = "status";
  private static $RESPONSE_MESSAGE_KEY = "message";
  private static $RESPONSE_PAYLOAD_KEY = "payload";

  public function setStatus($status): self {
    $this->setData(self::$RESPONSE_STATUS_KEY, $status);
    return $this;
  }

  public function setMessage(string $message): self {
    $this->setData(self::$RESPONSE_MESSAGE_KEY, $message);
    return $this;
  }

  public function setPayload(array $payload): self {
    $this->setData(self::$RESPONSE_PAYLOAD_KEY, $payload);
    return $this;
  }

  public function getStatus() {
    return $this->getData(self::$RESPONSE_STATUS_KEY);
  }

  public function getMessage(): ?string {
    return $this->getData(self::$RESPONSE_MESSAGE_KEY);
  }

  public function getPayload(): ?array {
    return $this->getData(self::$RESPONSE_PAYLOAD_KEY);
  }
}