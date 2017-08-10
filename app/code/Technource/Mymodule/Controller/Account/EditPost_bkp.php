<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Technource\Mymodule\Controller\Account;


/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EditPost extends \Magento\Customer\Controller\Account\EditPost
{
    
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->formKeyValidator->validate($this->getRequest())) {
			echo "hi1"; exit;
            return $resultRedirect->setPath('*/*/edit');
        }

        if ($this->getRequest()->isPost()) {
            $currentCustomerDataObject = $this->getCustomerDataObject($this->session->getCustomerId());
            $customerCandidateDataObject = $this->populateNewCustomerDataObject(
                $this->_request,
                $currentCustomerDataObject
            );

            // Change customer password
            if ($this->getRequest()->getParam('change_password')) {
                $this->changeCustomerPassword($currentCustomerDataObject->getEmail());
            }

            try {
                $this->customerRepository->save($customerCandidateDataObject);
            } catch (AuthenticationException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (InputException $e) {
                $this->messageManager->addException($e, __('Invalid input'));
            } catch (\Exception $e) {
                $message = __('We can\'t save the customer.')
                    . $e->getMessage()
                    . '<pre>' . $e->getTraceAsString() . '</pre>';
                $this->messageManager->addException($e, $message);
            }

            if ($this->messageManager->getMessages()->getCount() > 0) {
                $this->session->setCustomerFormData($this->getRequest()->getPostValue());
				echo "hi2"; exit;
                return $resultRedirect->setPath('*/*/edit');
            }

            $this->messageManager->addSuccess(__('You saved the account information.'));
            //return $resultRedirect->setPath('customer/account');
			if ($this->getRequest()->getParam('cp')) {
				return $resultRedirect->setPath('*/*/changepassword');
			}else{
				return $resultRedirect->setPath('customer/account/edit');
			}
        }
		if ($this->getRequest()->getParam('cp')) {
			return $resultRedirect->setPath('*/*/changepassword');
		}else{
			return $resultRedirect->setPath('customer/account/edit');
		}
    }

    
}
