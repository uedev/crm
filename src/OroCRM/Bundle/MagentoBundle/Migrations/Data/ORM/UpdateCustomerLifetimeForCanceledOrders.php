<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use OroCRM\Bundle\MagentoBundle\Entity\Order;
use OroCRM\Bundle\MagentoBundle\Entity\Repository\OrderRepository;

/**
 * Recalculate lifetime value for customers who has canceled orders.
 */
class UpdateCustomerLifetimeForCanceledOrders extends AbstractFixture
{
    const BUFFER_SIZE = 25;

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var EntityManager $manager */
        /** @var OrderRepository $orderRepository */
        $orderRepository = $manager->getRepository('OroCRMMagentoBundle:Order');
        $queryBuilder = $manager->createQueryBuilder('c');
        $queryBuilder->select('c')
            ->from('OroCRMMagentoBundle:Customer', 'c')
            ->join('OroCRMMagentoBundle:Order', 'o', 'WITH', 'o.customer = c')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('o.status', ':status')
                )
            )
            ->setParameter('status', Order::STATUS_CANCELED)
            ->groupBy('c');

        $iterator = new BufferedQueryResultIterator($queryBuilder);
        $iterator->setBufferSize(self::BUFFER_SIZE);

        $processed = 0;
        foreach ($iterator as $customer) {
            $oldLifetime = (float)$customer->getLifetime();
            $newLifetime = $orderRepository->getCustomerOrdersSubtotalAmount($customer);
            if ($newLifetime !== $oldLifetime) {
                $customer->setLifetime($newLifetime);
                $manager->persist($customer);
                $processed++;
            }

            if ($processed === self::BUFFER_SIZE) {
                $manager->flush();
                $manager->clear();
                $processed = 0;
            }
        }

        if ($processed > 0) {
            $manager->flush();
            $manager->clear();
        }
    }
}
