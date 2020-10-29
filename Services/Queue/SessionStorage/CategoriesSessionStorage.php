<?php

namespace Wizzy\Search\Services\Queue\SessionStorage;

/**
 * Storing all required categories in session for queue processing.
 *
 * Class CategoriesSessionStorages
 */
class CategoriesSessionStorage implements QueueSessionStorageInterface
{
    private static $categories;

    public function set(array $categories)
    {
        self::$categories = $categories;
    }

    public function getByIds(array $ids)
    {
        $categories = [];
        foreach ($ids as $id) {
            if (isset(self::$categories[$id])) {
                $categories[] = self::$categories[$id];
            }
        }

        return $categories;
    }

    public function get($id)
    {
        return (isset(self::$categories[$id])) ? self::$categories[$id] : null;
    }
}
