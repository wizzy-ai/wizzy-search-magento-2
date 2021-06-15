Wizzy Search & Autocomplete for Magento 2
==================

---
**Note:** 
We've branched Wizzy module to support **Magento Version >= 2.2.4 AND <= 2.2.10** with **PHP 7.0 / 7.1**

We do not distribute this releases separately through packagist and urge you to clone the repo and install the module.

Install module from this branch **only if** your server and magento version falls in above range. Otherwise our stable releases works well with latest Magento (>= M2.2.11) and PHP (>=7.2) versions.

Please do not consider these versions as stable releases and consider upgrading your outdated Magento and PHP.

---

Wizzy is an enterprise grade AI-Powered eCommerce search engine. It's a NLP enabled search which understands the user's intent and helps them to discover your store's products rapidly.

<img src="https://wizzy.ai/images/m2screenshot.png" />

Wizzy helps to increase your sales and retain your customers. It replaces the default Magento Search with following features.

- Self Learning & NLP Enabled Search
- Smart Autocomplete
- Spell Check & Correction
- Synonyms
- Advanced Facets & Filters
- 360° Analytics
- SEO Friendly
- Mobile Friendly
- Multiple Currencies

**If you own a Fashion eCommerce store, then Wizzy is go-to solution for you.** Visit [Features](https://wizzy.ai/features) to understand more about Wizzy.

This module comes with following features including above for Magento 2.

- Search Results Page
- Category Pages
- 100% Customisable UI
- Advance Data Structuring
- Priority Support   

## Instllation

Wizzy Search Magento 2 module can be installed following ways. 

1. Using Composer
2. From Magento Marketplace 

### Composer

````
composer require wizzy/search-magento-2
````

Once the module is installed through composer execute following commands to enable it on Magento store.

````
php bin/magento module:enable Wizzy_Search
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy
````

### Magento Marketplace

[Click Here](https://marketplace.magento.com/wizzy-search-magento-2.html) to Visit Magento Marketplace and install the module, follow the instructions on the marketplace for installation guide.

## Demo Store
Visit [Demo Store](http://magento.demostore.wizzy.ai) to check Autocomplete and Instant Search functionality. To test the admin related functionalitites you must install this extension on your store try yourself.

## Want to Contribute?
Found bug or improvement in the module and wants to contribute? We're glad you wants to, Please follow the process.

1. Make sure you've latest changes in `dev` branch.
2. Checkout new branch from `dev` branch.
3. Do your fixes/changes.
4. Make sure the changes you made in code is of Magento2 PHPCS standard.
5. Create PR. 

## Found Bugs? Need Help? Have Questions? 
If you’ve found any bug in the module, you can [create an issue](https://github.com/wizzy-ai/wizzy-search-magento-2/issues) in this Repository, and our team will look into it as quickly as possible.

You can also contact us on [team@wizzy.ai](mailto:team@wizzy.ai)
