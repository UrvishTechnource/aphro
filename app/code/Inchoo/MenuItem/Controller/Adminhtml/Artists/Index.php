<?php
namespace Inchoo\MenuItem\Controller\Adminhtml\Artists;
class Index extends \Magento\Backend\App\Action
{
  protected $resultPageFactory;
  public function __construct(
      \Magento\Backend\App\Action\Context $context,
      \Magento\Framework\View\Result\PageFactory $resultPageFactory
  ) {
       parent::__construct($context);
       $this->resultPageFactory = $resultPageFactory;
  }

  public function execute()
  {
    $resultPage = $this->resultPageFactory->create();
    $resultPage->setActiveMenu('Inchoo_MenuItem::first_level_demo');
    $resultPage->addBreadcrumb(__('All Artist'), __('All Artist'));
    $resultPage->addBreadcrumb(__('All Artist'), __('All Artist'));
    $resultPage->getConfig()->getTitle()->prepend(__('All Artist'));
    
    return  $resultPage;
  }
}
?>