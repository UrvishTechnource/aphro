<?php

namespace Inchoo\MenuItem\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class FeaturedArtist extends Column {

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    public function __construct(
    ContextInterface $context, UiComponentFactory $uiComponentFactory, UrlInterface $urlBuilder, array $components = [], array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource) {
        if (isset($dataSource['data']['items'])) {
            $key_array = array();
            $i = 0;
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $resourceManager = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resourceManager->getConnection();
            $tableName = "artist_list";
            $artistList = $connection->fetchAll("SELECT GROUP_CONCAT(artist_id) AS artist_ids FROM $tableName WHERE featured_status = 1");
            $artistList = explode(",", $artistList[0]['artist_ids']);
            foreach ($dataSource['data']['items'] as & $item) {
                $customerID = $item['art_user'];
                if (!in_array($customerID, $key_array)) {

                    array_push($key_array, $customerID);
//                    $customerRepository = $objectManager->get('Magento\Customer\Api\CustomerRepositoryInterface');
                    if ($objectManager->get('Magento\Customer\Model\Customer')->load($customerID)) {                        
                        $label = __('Mark as Featured');
                        $newStatus = 1;
                        if (in_array($customerID, $artistList)) {
                            $label = __('Mark as Unfeatured');
                            $newStatus = 0;
                        }
                        $item[$this->getData('name')]['edit'] = [
                            'href' => $this->urlBuilder->getUrl(
                                    'menuitem/artists/toggleFeatured', ['id' => $customerID,'newstatus'=>$newStatus]
                            ),
                            'label' => $label,
                            'hidden' => false,
                        ];
//                        $item['Feature'] = $artist_first_name . ' ' . $artist_last_name;
                    } else {
                        unset($dataSource['data']['items'][$i]);
                    }
                } else {
                    unset($dataSource['data']['items'][$i]);
                }
                $i++;
            }
            $dataSource['data']['items'] = array_values($dataSource['data']['items']);

//            echo "<pre>";
//            var_dump($dataSource);
//            die();
        }
        return $dataSource;
    }

}
