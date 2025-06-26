<?php
namespace Wizzy\Search\Model\ResourceModel\Synonym;

use Magento\Framework\App\RequestInterface;
use Wizzy\Search\Services\API\Wizzy\WizzyAPIWrapper;

class DataProvider
{
    protected $request;
    protected $wizzyApiWrapper;

    public function __construct(
        RequestInterface $request,
        WizzyAPIWrapper $wizzyApiWrapper
    ) {
        $this->request = $request;
        $this->wizzyApiWrapper = $wizzyApiWrapper;
    }

    public function getSynonyms()
    {
        $storeId = (int) $this->request->getParam('store', 1);
        $response = $this->wizzyApiWrapper->getSynonyms($storeId);
       
        return $response;
    }
}
