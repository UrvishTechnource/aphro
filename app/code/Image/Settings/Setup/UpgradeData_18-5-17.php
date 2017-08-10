<?php
namespace Image\Settings\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Model\Config;

class UpgradeData implements UpgradeDataInterface
{
	private $eavSetupFactory;

	public function __construct(EavSetupFactory $eavSetupFactory, Config $eavConfig)
	{
		$this->eavSetupFactory = $eavSetupFactory;
		$this->eavConfig = $eavConfig;
	}

	public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
	{
 
        if (version_compare($context->getVersion(), '1.0.8', '<=')) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
			$eavSetup->addAttribute(
			\Magento\Customer\Model\Customer::ENTITY,
			'upload_watermark',
			[
				'type' => 'int',
				'label' => 'Zoom View Watermark',
				'input' => 'select',
				'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
				'required' => false,
				'default' => '',
				'sort_order' => 100,
				'system' => false,
				'position' => 100
			]
		);
		$upload_watermark = $this->eavConfig->getAttribute(\Magento\Customer\Model\Customer::ENTITY, 'upload_watermark');
		$upload_watermark->setData(
			'used_in_forms',
			['adminhtml_customer', 'customer_account_edit']
		)->setData("is_user_defined", 1);
		$upload_watermark->save();
        }
 
       
		
	}
}
