<?php
namespace Techprof\EditProf\Setup;

use Magento\Framework\Module\Setup\Migration;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;





class InstallData implements InstallDataInterface
{
    /**
     * Customer setup factory
     *
     * @var \Magento\Customer\Setup\CustomerSetupFactory
     */
    private $customerSetupFactory;
    /**
     * Init
     *
     * @param \Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(\Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory)
    {
        $this->customerSetupFactory = $customerSetupFactory;
    }
    /**
     * Installs DB schema for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {

        $installer = $setup;
        $installer->startSetup();

        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        $entityTypeId = $customerSetup->getEntityTypeId(\Magento\Customer\Model\Customer::ENTITY);
        $customerSetup->removeAttribute(\Magento\Customer\Model\Customer::ENTITY, "prof_image");

        $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, "prof_image",  array(
            "type"     => "varchar",
            "backend"  => "",
            "label"    => "Profile Image",
            "input"    => "image",
            "source"   => "",
            "visible"  => true,
            "required" => false,
            "default" => "",
            "frontend" => "",
            "unique"     => true,
            "note"       => ""

        ));

        $prof_image   = $customerSetup->getAttribute(\Magento\Customer\Model\Customer::ENTITY, "prof_image");

        $prof_image = $customerSetup->getEavConfig()->getAttribute(\Magento\Customer\Model\Customer::ENTITY, 'prof_image');
        $used_in_forms[]="adminhtml_customer";
        //$used_in_forms[]="checkout_register";
        //$used_in_forms[]="customer_account_create";
        $used_in_forms[]="customer_account_edit";
        //$used_in_forms[]="adminhtml_checkout";
        $prof_image->setData("used_in_forms", $used_in_forms)
            ->setData("is_used_for_customer_segment", true)
            ->setData("is_system", 0)
            ->setData("is_user_defined", 1)
            ->setData("is_visible", 1)
            ->setData("sort_order", 100);

        $prof_image->save();
        
        $customerSetup->removeAttribute(\Magento\Customer\Model\Customer::ENTITY, "cover_image");

        $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, "cover_image",  array(
            "type"     => "varchar",
            "backend"  => "",
            "label"    => "Cover Image",
            "input"    => "image",
            "source"   => "",
            "visible"  => true,
            "required" => false,
            "default" => "",
            "frontend" => "",
            "unique"     => true,
            "note"       => ""

        ));

        $cover_image   = $customerSetup->getAttribute(\Magento\Customer\Model\Customer::ENTITY, "cover_image");

        $cover_image = $customerSetup->getEavConfig()->getAttribute(\Magento\Customer\Model\Customer::ENTITY, 'cover_image');
        $used_in_forms1[]="adminhtml_customer";
        //$used_in_forms[]="checkout_register";
        //$used_in_forms[]="customer_account_create";
        $used_in_forms1[]="customer_account_edit";
        //$used_in_forms[]="adminhtml_checkout";
        $cover_image->setData("used_in_forms", $used_in_forms1)
            ->setData("is_used_for_customer_segment", true)
            ->setData("is_system", 0)
            ->setData("is_user_defined", 1)
            ->setData("is_visible", 1)
            ->setData("sort_order", 100);

        $cover_image->save();
        
        $customerSetup->removeAttribute(\Magento\Customer\Model\Customer::ENTITY, "description");

        $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, "description",  array(
            "type"     => "varchar",
            "backend"  => "",
            "label"    => "Description",
            "input"    => "textarea",
            "source"   => "",
            "visible"  => true,
            "required" => false,
            "default" => "",
            "frontend" => "",
            "unique"     => false,
            "note"       => ""

        ));

        $description   = $customerSetup->getAttribute(\Magento\Customer\Model\Customer::ENTITY, "description");

        $description = $customerSetup->getEavConfig()->getAttribute(\Magento\Customer\Model\Customer::ENTITY, 'description');
        $used_in_forms2[]="adminhtml_customer";
        //$used_in_forms[]="checkout_register";
        //$used_in_forms[]="customer_account_create";
        $used_in_forms2[]="customer_account_edit";
        //$used_in_forms[]="adminhtml_checkout";
        $description->setData("used_in_forms", $used_in_forms2)
            ->setData("is_used_for_customer_segment", true)
            ->setData("is_system", 0)
            ->setData("is_user_defined", 1)
            ->setData("is_visible", 1)
            ->setData("sort_order", 100);

        $description->save();
        
        $customerSetup->removeAttribute(\Magento\Customer\Model\Customer::ENTITY, "facebook");

        $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, "facebook",  array(
            "type"     => "varchar",
            "backend"  => "",
            "label"    => "Facebook URL",
            "input"    => "text",
            "source"   => "",
            "visible"  => true,
            "required" => false,
            "default" => "",
            "frontend" => "",
            "unique"     => false,
            "note"       => ""

        ));

        $facebook   = $customerSetup->getAttribute(\Magento\Customer\Model\Customer::ENTITY, "facebook");

        $facebook = $customerSetup->getEavConfig()->getAttribute(\Magento\Customer\Model\Customer::ENTITY, 'facebook');
        $used_in_forms3[]="adminhtml_customer";
        //$used_in_forms[]="checkout_register";
        //$used_in_forms[]="customer_account_create";
        $used_in_forms3[]="customer_account_edit";
        //$used_in_forms[]="adminhtml_checkout";
        $facebook->setData("used_in_forms", $used_in_forms3)
            ->setData("is_used_for_customer_segment", true)
            ->setData("is_system", 0)
            ->setData("is_user_defined", 1)
            ->setData("is_visible", 1)
            ->setData("sort_order", 100);

        $facebook->save();
        
        $customerSetup->removeAttribute(\Magento\Customer\Model\Customer::ENTITY, "twitter");

        $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, "twitter",  array(
            "type"     => "varchar",
            "backend"  => "",
            "label"    => "Twitter URL",
            "input"    => "text",
            "source"   => "",
            "visible"  => true,
            "required" => false,
            "default" => "",
            "frontend" => "",
            "unique"     => false,
            "note"       => ""

        ));

        $twitter   = $customerSetup->getAttribute(\Magento\Customer\Model\Customer::ENTITY, "twitter");

        $twitter = $customerSetup->getEavConfig()->getAttribute(\Magento\Customer\Model\Customer::ENTITY, 'twitter');
        $used_in_forms4[]="adminhtml_customer";
        //$used_in_forms[]="checkout_register";
        //$used_in_forms[]="customer_account_create";
        $used_in_forms4[]="customer_account_edit";
        //$used_in_forms[]="adminhtml_checkout";
        $twitter->setData("used_in_forms", $used_in_forms4)
            ->setData("is_used_for_customer_segment", true)
            ->setData("is_system", 0)
            ->setData("is_user_defined", 1)
            ->setData("is_visible", 1)
            ->setData("sort_order", 100);

        $twitter->save();
        
        $customerSetup->removeAttribute(\Magento\Customer\Model\Customer::ENTITY, "instagram");

        $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, "instagram",  array(
            "type"     => "varchar",
            "backend"  => "",
            "label"    => "Instagram URL",
            "input"    => "text",
            "source"   => "",
            "visible"  => true,
            "required" => false,
            "default" => "",
            "frontend" => "",
            "unique"     => false,
            "note"       => ""

        ));

        $instagram   = $customerSetup->getAttribute(\Magento\Customer\Model\Customer::ENTITY, "instagram");

        $instagram = $customerSetup->getEavConfig()->getAttribute(\Magento\Customer\Model\Customer::ENTITY, 'instagram');
        $used_in_forms5[]="adminhtml_customer";
        //$used_in_forms[]="checkout_register";
        //$used_in_forms[]="customer_account_create";
        $used_in_forms5[]="customer_account_edit";
        //$used_in_forms[]="adminhtml_checkout";
        $instagram->setData("used_in_forms", $used_in_forms5)
            ->setData("is_used_for_customer_segment", true)
            ->setData("is_system", 0)
            ->setData("is_user_defined", 1)
            ->setData("is_visible", 1)
            ->setData("sort_order", 100);

        $instagram->save();
        
        $customerSetup->removeAttribute(\Magento\Customer\Model\Customer::ENTITY, "pinterest");

        $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, "pinterest",  array(
            "type"     => "varchar",
            "backend"  => "",
            "label"    => "Pinterest URL",
            "input"    => "text",
            "source"   => "",
            "visible"  => true,
            "required" => false,
            "default" => "",
            "frontend" => "",
            "unique"     => false,
            "note"       => ""

        ));

        $pinterest   = $customerSetup->getAttribute(\Magento\Customer\Model\Customer::ENTITY, "pinterest");

        $pinterest = $customerSetup->getEavConfig()->getAttribute(\Magento\Customer\Model\Customer::ENTITY, 'pinterest');
        $used_in_forms6[]="adminhtml_customer";
        //$used_in_forms[]="checkout_register";
        //$used_in_forms[]="customer_account_create";
        $used_in_forms6[]="customer_account_edit";
        //$used_in_forms[]="adminhtml_checkout";
        $pinterest->setData("used_in_forms", $used_in_forms6)
            ->setData("is_used_for_customer_segment", true)
            ->setData("is_system", 0)
            ->setData("is_user_defined", 1)
            ->setData("is_visible", 1)
            ->setData("sort_order", 100);

        $pinterest->save();
        
        
        



        $installer->endSetup();
    }
}
