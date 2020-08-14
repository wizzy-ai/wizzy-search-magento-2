<?php

namespace Wizzy\Search\Helpers\API;

class WizzyAPIEndPoints {
  private static $baseEndPoint = "https://api.wizzy.ai/v1";

  public static function base(): string {
    return self::$baseEndPoint;
  }

  public static function storeAuth(): string {
    return self::getStoresBase() . '/auth';
  }

  public static function saveProducts(): string {
    return self::getProductsBase() . '/save';
  }

  public static function deleteProducts(): string {
    return self::getProductsBase() . '/delete';
  }

  public static function setDefaultCurrency(): string {
     return self::getCurrenciesBase() . '/default-currency';
  }

  public static function setDisplayCurrency(): string {
     return self::getCurrenciesBase() . '/display-currency';
  }

  public static function saveCurrencies(): string {
     return self::getCurrenciesBase() . '/';
  }

  public static function savePages(): string {
     return self::getPagesBase() . '/';
  }

  public static function saveCurrencyRates(): string {
     return self::getCurrenciesBase() . '/rates';
  }

   public static function deleteCurrencies(): string {
      return self::getCurrenciesBase() . '/';
   }

   public static function deletePages(): string {
      return self::getPagesBase() . '/';
   }

   public static function getCurrencies(): string {
      return self::getCurrenciesBase() . '/';
   }

   public static function getPages(): string {
      return self::getPagesBase() . '/';
   }

  private static function getStoresBase() {
    return self::base().'/stores';
  }

  private static function getProductsBase() {
    return self::base().'/products';
  }

   private static function getCurrenciesBase() {
      return self::base().'/currencies';
   }

   private static function getPagesBase() {
      return self::base().'/pages';
   }
}