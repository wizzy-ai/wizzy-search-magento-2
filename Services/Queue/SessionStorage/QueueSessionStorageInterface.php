<?php

namespace Wizzy\Search\Services\Queue\SessionStorage;

/**
 * An implementation of the session storage used during Queue processing.
 *
 * Interface QueueSessionStorageInterface
 */
interface QueueSessionStorageInterface
{

   /**
    * Set an array of session data.
    * @param array $data
    * @return mixed
    */
    public function set(array $data);

   /**
    * Reterive stored session data based on given ids.
    * @param array $ids
    * @return mixed
    */
    public function getByIds(array $ids);

   /**
    * Retrieve single session data based on given id.
    *
    * @param $id
    * @return mixed
    */
    public function get($id);
}
