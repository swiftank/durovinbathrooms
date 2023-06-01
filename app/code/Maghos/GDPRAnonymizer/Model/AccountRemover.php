<?php
/**
 *
 * Maghos_Gdpr Magento 2 extension
 *
 * NOTICE OF LICENSE
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 *
 * @category Maghos
 * @package Maghos_Gdpr
 * @copyright Copyright (c) 2018 Maghos s.r.o.
 * @license http://www.maghos.com/business-license
 * @author Magento dev team <support@maghos.com>
 */
namespace Maghos\Gdpr\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;

class AccountRemover
{

    /** @var \Maghos\Gdpr\Model\Validation */
    private $validation;

    /** @var \Maghos\Gdpr\Model\DataRemover */
    private $dataRemover;

    /** @var \Maghos\Gdpr\Helper\Data */
    private $helper;

    /** @var \Magento\Framework\App\ResourceConnection */
    private $connection;

    /** @var \Magento\Framework\Registry */
    private $registry;

    /** @var \Magento\Customer\Api\CustomerRepositoryInterface */
    private $customerRepository;

    /**
     * AccountRemover constructor.
     * @param \Maghos\Gdpr\Model\Validation $validation
     * @param \Maghos\Gdpr\Model\DataRemover $dataRemover
     * @param \Maghos\Gdpr\Helper\Data $helper
     * @param \Magento\Framework\App\ResourceConnection $connection
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        \Maghos\Gdpr\Model\Validation $validation,
        \Maghos\Gdpr\Model\DataRemover $dataRemover,
        \Maghos\Gdpr\Helper\Data $helper,
        \Magento\Framework\App\ResourceConnection $connection,
        \Magento\Framework\Registry $registry,
        CustomerRepositoryInterface $customerRepository
    ) {

        $this->validation         = $validation;
        $this->dataRemover        = $dataRemover;
        $this->helper             = $helper;
        $this->connection         = $connection;
        $this->registry           = $registry;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param $customerId
     * @param $hash
     * @return bool
     * @throws \Exception
     */
    public function removeCustomerAccount($customerId, $hash)
    {
        $this->validateHash($customerId, $hash);

        $customer = $this->customerRepository->getById($customerId);
        $email    = $customer->getEmail();

        $connection = $this->connection->getConnection('write');

        try {
            $connection->beginTransaction();

            $this->dataRemover->removeDataForCustomer($customer->getId());
            $this->registry->register('isSecureArea', true);
            $this->customerRepository->delete($customer);
            $this->registry->unregister('isSecureArea');
            $this->dataRemover->removeDataForEmail($email);

            $connection->commit();
            return true;
        } catch (\Exception $e) {
            $connection->rollBack();

            throw $e;
        }
    }

    /**
     * @param $email
     * @param $hash
     * @throws \Exception
     */
    public function removePersonalInformation($email, $hash)
    {
        $this->validateHash($email, $hash);
        $connection = $this->connection->getConnection('write');

        try {

            $connection->beginTransaction();

            $this->dataRemover->removeDataForEmail($email);

            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();

            throw $e;
        }
    }

    /**
     * Validate hash information.
     *
     * @param $text
     * @param $hash
     * @throws \Exception
     */
    private function validateHash($text, $hash)
    {
        $result = $this->validation->verify($text, $hash);
        if (!$result) {
            throw new \Exception('Incorrect hash for text ' . $text);
        }
    }
}
