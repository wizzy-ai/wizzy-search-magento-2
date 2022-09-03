<?php

namespace Wizzy\Search\Helpers\API;

class WizzyAPIEndPoints
{
    const BASE_END_POINT = "https://api.wizsearch.in/v1";
    const STORES_BASE_AUTH = self::BASE_END_POINT.'/stores';
    const PRODUCTS_BASE_AUTH = self::BASE_END_POINT.'/products';
    const CURRENCIES_BASE_AUTH = self::BASE_END_POINT.'/currencies';
    const PAGES_BASE_AUTH = self::BASE_END_POINT.'/pages';
    const EVENTS_BASE_AUTH = self::BASE_END_POINT.'/events';

    const STORE_AUTH = self::STORES_BASE_AUTH . '/auth';

    const SAVE_PRODUCTS = self::PRODUCTS_BASE_AUTH . '/save';
    const DELETE_PRODUCTS = self::PRODUCTS_BASE_AUTH . '/delete';

    const SET_DEFAULT_CURRENCY = self::CURRENCIES_BASE_AUTH . '/default-currency';
    const SET_DISPLAY_CURRENCY = self::CURRENCIES_BASE_AUTH . '/display-currency';
    const SAVE_CURRENCIES = self::CURRENCIES_BASE_AUTH . '/';
    const GET_CURRENCIES = self::CURRENCIES_BASE_AUTH . '/';
    const SAVE_CURRENCIES_RATES = self::CURRENCIES_BASE_AUTH . '/rates';
    const DELETE_CURRENCIES = self::CURRENCIES_BASE_AUTH . '/';

    const SAVE_PAGES = self::PAGES_BASE_AUTH . '/';
    const GET_PAGES = self::PAGES_BASE_AUTH . '/';
    const DELETE_PAGES = self::PAGES_BASE_AUTH . '/';

    const COLLECT_CLICK_EVENT = self::EVENTS_BASE_AUTH . '/click';
    const COLLECT_VIEW_EVENT = self::EVENTS_BASE_AUTH . '/view';
    const COLLECT_CONVERTED_EVENT = self::EVENTS_BASE_AUTH . '/converted';
}
