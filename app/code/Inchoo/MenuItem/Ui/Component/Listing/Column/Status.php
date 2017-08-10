<?php

namespace Inchoo\MenuItem\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Status extends Column {

    public function __construct(
    ContextInterface $context, UiComponentFactory $uiComponentFactory, array $components = [], array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource) {
        if (isset($dataSource['data']['items'])) {
            $key_array = array();
            $i = 0;
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            foreach ($dataSource['data']['items'] as & $item) {
                $customerID = $item['art_user'];
                if (!in_array($customerID, $key_array)) {

                    array_push($key_array, $customerID);
//                    $customerRepository = $objectManager->get('Magento\Customer\Api\CustomerRepositoryInterface');
                    if ($objectManager->get('Magento\Customer\Model\Customer')->load($customerID)) {
                        $customer_obj = $objectManager->get('Magento\Customer\Model\Customer')->load($customerID);
                        $artist_first_name = $customer_obj->getData('firstname');
                        $artist_last_name = $customer_obj->getData('lastname');
                        $item[$this->getData('name')] = $artist_first_name . ' ' . $artist_last_name;
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
