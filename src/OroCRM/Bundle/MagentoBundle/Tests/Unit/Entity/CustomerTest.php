<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Entity;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;

class CustomerTest extends AbstractEntityTestCase
{
    const TEST_ORIGIN_ID = 123;
    const TEST_IS_ACTIVE = false;
    const TEST_STRING    = 'string';

    /** @var Customer */
    protected $entity;

    /**
     * {@inheritDoc}
     */
    public function getEntityFQCN()
    {
        return 'OroCRM\Bundle\MagentoBundle\Entity\Customer';
    }

    /**
     * {@inheritDoc}
     */
    public function getSetDataProvider()
    {
        $date         = new \DateTime('now');
        $group        = $this->getMock('OroCRM\Bundle\MagentoBundle\Entity\CustomerGroup');
        $website      = $this->getMock('OroCRM\Bundle\MagentoBundle\Entity\Website');
        $store        = $this->getMock('OroCRM\Bundle\MagentoBundle\Entity\Store');
        $contact      = $this->getMock('OroCRM\Bundle\ContactBundle\Entity\Contact');
        $account      = $this->getMock('OroCRM\Bundle\AccountBundle\Entity\Account');
        $owner        = $this->getMock('Oro\Bundle\UserBundle\Entity\User');
        $organization = $this->getMock('Oro\Bundle\OrganizationBundle\Entity\Organization');

        return [
            'createdAt'    => ['createdAt', $date, $date],
            'updatedAt'    => ['updatedAt', $date, $date],
            'group'        => ['group', $group, $group],
            'website'      => ['website', $website, $website],
            'store'        => ['store', $store, $store],
            'contact'      => ['contact', $contact, $contact],
            'account'      => ['account', $account, $account],
            'originId'     => ['originId', self::TEST_ORIGIN_ID, self::TEST_ORIGIN_ID],
            'vat'          => ['vat', self::TEST_STRING . 'vat', self::TEST_STRING . 'vat'],
            'isActive'     => ['isActive', self::TEST_IS_ACTIVE, self::TEST_IS_ACTIVE],
            'owner'        => ['owner', $owner, $owner],
            'organization' => ['organization', $organization, $organization],
            'recency'      => ['recency', 1, 1],
            'frequency'    => ['frequency', 2, 2],
            'monetary'     => ['monetary', 3, 3],
            'syncState'    => ['syncState', 1, 1]
        ];
    }

    public function testGettersSetters()
    {
        $this->entity->setFirstName(self::TEST_STRING . 'first');
        $this->entity->setLastName(self::TEST_STRING . 'last');
        $this->assertNull($this->entity->getOrganization());

        $this->entity->addAddress($this->getMock('OroCRM\Bundle\MagentoBundle\Entity\Address'));
        $this->entity->setOrganization($this->getMock('Oro\Bundle\OrganizationBundle\Entity\Organization'));


        $this->assertInstanceOf('Doctrine\Common\Collections\Collection', $this->entity->getAddresses());
        $this->assertInstanceOf('Oro\Bundle\OrganizationBundle\Entity\Organization', $this->entity->getOrganization());
        $this->assertFalse($this->entity->getAddressByOriginId(1));
    }

    public function getGetWebsiteName()
    {
        $this->assertNull($this->entity->getWebsiteName());

        $expectedValue = 'test';
        $website = $this->getMock('OroCRM\Bundle\MagentoBundle\Entity\Website');
        $website->expects($this->once())
            ->method('getName')
            ->will($this->returnValue($expectedValue));

        $this->assertEquals($expectedValue, $website->getWebsiteName());
    }

    public function getGetStoreName()
    {
        $this->assertNull($this->entity->getStoreName());

        $expectedValue = 'test';
        $website = $this->getMock('OroCRM\Bundle\MagentoBundle\Entity\Store');
        $website->expects($this->once())
            ->method('getName')
            ->will($this->returnValue($expectedValue));

        $this->assertEquals($expectedValue, $website->getStoreName());
    }
}
