<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Provider;

use OroCRM\Bundle\MagentoBundle\Command\InitialSyncCommand;
use OroCRM\Bundle\MagentoBundle\Entity\MagentoSoapTransport;
use OroCRM\Bundle\MagentoBundle\Provider\AbstractInitialProcessor;
use OroCRM\Bundle\MagentoBundle\Provider\AbstractMagentoConnector;
use OroCRM\Bundle\MagentoBundle\Provider\InitialScheduleProcessor;

class InitialScheduleProcessorTest extends AbstractSyncProcessorTest
{
    /** @var InitialScheduleProcessor */
    protected $processor;

    protected function setUp()
    {
        parent::setUp();

        $this->processor = new InitialScheduleProcessor(
            $this->registry,
            $this->processorRegistry,
            $this->jobExecutor,
            $this->typesRegistry,
            $this->eventDispatcher,
            $this->logger
        );

        $this->processor->setChannelClassName('Oro\IntegrationBundle\Entity\Channel');
    }

    public function testProcessFirstInitial()
    {
        $connector = 'testConnector_initial';
        $connectors = [$connector];

        $syncStartDate = new \DateTime('2000-01-01 00:00:00', new \DateTimeZone('UTC'));
        $integration = $this->getIntegration($connectors, $syncStartDate);

        $this->em->expects($this->exactly(2))
            ->method('persist');
        $this->em->expects($this->exactly(2))
            ->method('flush');
        $this->repository->expects($this->once())
            ->method('getRunningSyncJobsCount')
            ->with(InitialSyncCommand::COMMAND_NAME, $integration->getId());

        $this->assertProcessCalls();
        $this->assertExecuteJob();

        $this->processor->process($integration);
    }

    public function testProcessExisting()
    {
        $syncedTo = new \DateTime('2011-01-02 12:13:14', new \DateTimeZone('UTC'));
        $initialStartDate = new \DateTime('2011-01-03 12:13:14', new \DateTimeZone('UTC'));
        $syncStartDate = new \DateTime('2000-01-01 00:00:00', new \DateTimeZone('UTC'));

        $connector = 'testConnector_initial';
        $connectors = [$connector];
        $integration = $this->getIntegration($connectors, $syncStartDate);
        /** @var MagentoSoapTransport $transport */
        $transport = $integration->getTransport();
        $transport->setInitialSyncStartDate($initialStartDate);

        $status = $this->getMockBuilder('Oro\Bundle\IntegrationBundle\Entity\Status')
            ->disableOriginalConstructor()
            ->getMock();
        $status->expects($this->atLeastOnce())
            ->method('getData')
            ->will(
                $this->returnValue(
                    [
                        AbstractInitialProcessor::INITIAL_SYNCED_TO => $syncedTo->format(\DateTime::ISO8601)
                    ]
                )
            );

        $this->assertConnectorStatusCall($integration, $connector, $status);

        $this->em->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf('JMS\JobQueueBundle\Entity\Job'));
        $this->em->expects($this->once())
            ->method('flush')
            ->with($this->isInstanceOf('JMS\JobQueueBundle\Entity\Job'));
        $this->repository->expects($this->once())
            ->method('getRunningSyncJobsCount')
            ->with(InitialSyncCommand::COMMAND_NAME, $integration->getId());

        $this->assertProcessCalls();
        $this->assertExecuteJob(
            [
                'processorAlias' => false,
                'entityName' => 'testEntity',
                'channel' => 'testChannel',
                'channelType' => 'testChannelType',
                AbstractMagentoConnector::LAST_SYNC_KEY => $initialStartDate
            ]
        );

        $this->processor->process($integration);
    }

    public function testProcessJobRunning()
    {
        $syncedTo = new \DateTime('2011-01-02 12:13:14', new \DateTimeZone('UTC'));
        $initialStartDate = new \DateTime('2011-01-03 12:13:14', new \DateTimeZone('UTC'));
        $syncStartDate = new \DateTime('2000-01-01 00:00:00', new \DateTimeZone('UTC'));

        $connector = 'testConnector';
        $connectors = [$connector];
        $integration = $this->getIntegration($connectors, $syncStartDate);

        /** @var MagentoSoapTransport $transport */
        $transport = $integration->getTransport();
        $transport->setInitialSyncStartDate($initialStartDate);

        $this->repository->expects($this->never())
            ->method('getLastStatusForConnector');

        $this->em->expects($this->never())
            ->method('persist');
        $this->em->expects($this->never())
            ->method('flush');
        $this->repository->expects($this->once())
            ->method('getRunningSyncJobsCount')
            ->with(InitialSyncCommand::COMMAND_NAME, $integration->getId())
            ->will($this->returnValue(2));

        $this->assertProcessCalls();
        $this->assertExecuteJob(
            [
                'processorAlias' => false,
                'entityName' => 'testEntity',
                'channel' => 'testChannel',
                'channelType' => 'testChannelType',
                AbstractMagentoConnector::LAST_SYNC_KEY => $initialStartDate
            ]
        );

        $this->processor->process($integration);
    }

    public function testProcessInitialSynced()
    {
        $initialStartDate = new \DateTime('2011-01-03 12:13:14', new \DateTimeZone('UTC'));
        $syncStartDate = new \DateTime('2000-01-01 00:00:00', new \DateTimeZone('UTC'));
        $syncedTo = $syncStartDate->sub(new \DateInterval('P1D'));

        $connector = 'testConnector_initial';
        $connectors = [$connector];
        $integration = $this->getIntegration($connectors, $syncStartDate);

        /** @var MagentoSoapTransport $transport */
        $transport = $integration->getTransport();
        $transport->setInitialSyncStartDate($initialStartDate);

        $status = $this->getMockBuilder('Oro\Bundle\IntegrationBundle\Entity\Status')
            ->disableOriginalConstructor()
            ->getMock();
        $status->expects($this->atLeastOnce())
            ->method('getData')
            ->will(
                $this->returnValue(
                    [
                        AbstractInitialProcessor::INITIAL_SYNCED_TO => $syncedTo->format(\DateTime::ISO8601)
                    ]
                )
            );

        $this->assertConnectorStatusCall($integration, $connector, $status);

        $this->em->expects($this->never())
            ->method('persist');
        $this->em->expects($this->never())
            ->method('flush');
        $this->repository->expects($this->once())
            ->method('getRunningSyncJobsCount')
            ->with(InitialSyncCommand::COMMAND_NAME, $integration->getId());


        $this->assertProcessCalls();
        $this->assertExecuteJob(
            [
                'processorAlias' => false,
                'entityName' => 'testEntity',
                'channel' => 'testChannel',
                'channelType' => 'testChannelType',
                AbstractMagentoConnector::LAST_SYNC_KEY => $initialStartDate
            ]
        );

        $this->processor->process($integration);
    }
}
