<?php

namespace Wizzy\Search\Services\Queue\SessionStorage;

/**
 * Storing all required products in session for queue processing.
 *
 * Class ProductsSessionStorage
 */
class ProductsSessionStorage implements QueueSessionStorageInterface
{
    private static $products;

    public function set(array $products)
    {
        self::$products = $products;
    }

    public function getByIds(array $ids)
    {
        $products = [];
        foreach ($ids as $id) {
            if (isset(self::$products[$id])) {
                $products[] = self::$products[$id];
            }
        }

        return $products;
    }

    public function get($id)
    {
        return (isset(self::$products[$id])) ? self::$products[$id] : null;
    }
}
