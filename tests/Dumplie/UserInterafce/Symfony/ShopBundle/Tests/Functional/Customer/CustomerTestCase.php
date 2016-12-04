<?php

declare (strict_types = 1);

namespace Dumplie\UserInterafce\Symfony\ShopBundle\Tests\Functional\Customer;

use Dumplie\Customer\Infrastructure\Doctrine\Dbal\Query\DbalCartQuery;
use Dumplie\Customer\Tests\Doctrine\ORMCustomerContext;
use Dumplie\Inventory\Tests\Doctrine\ORMInventoryContext;
use Dumplie\SharedKernel\Application\CommandBus;
use Dumplie\SharedKernel\Application\Services;
use Dumplie\SharedKernel\Infrastructure\InMemory\InMemoryEventLog;
use Dumplie\SharedKernel\Tests\Context\CommandBusFactory;
use Dumplie\SharedKernel\Tests\Doctrine\ORMHelper;
use Dumplie\UserInterafce\Symfony\ShopBundle\Tests\Functional\WebTestCase;

class CustomerTestCase extends WebTestCase
{
    use ORMHelper;

    /**
     * @var ORMInventoryContext
     */
    protected $inventoryContext;

    /**
     * @var ORMCustomerContext
     */
    protected $customerContext;

    public function setUp()
    {
        parent::setUp();

        $em = $this->getContainer()->get('doctrine')->getManager();
        $commandBus = $this->getContainer()->get(Services::KERNEL_COMMAND_BUS);

        $this->dropSchema($em);
        $this->createSchema($em);

        $this->inventoryContext = new ORMInventoryContext(
            $em,
            new InMemoryEventLog(),
            $this->createCommandBusFactory($commandBus)
        );

        $this->customerContext = new ORMCustomerContext(
            $em,
            new InMemoryEventLog(),
            $this->createCommandBusFactory($commandBus)
        );

        $this->inventoryContext->addProduct('DUMPLIE_SKU', 100, 'USD', true);
    }

    /**
     * @param CommandBus $commandBus
     *
     * @return CommandBusFactory
     */
    private function createCommandBusFactory(CommandBus $commandBus): CommandBusFactory
    {
        return new class($commandBus) implements CommandBusFactory
        {
            private $commandBus;

            public function __construct(CommandBus $commandBus)
            {
                $this->commandBus = $commandBus;
            }

            public function create(array $handlers = [], array $commandExtension = []): CommandBus
            {
                return $this->commandBus;
            }
        };
    }

    /**
     * @return DbalCartQuery
     */
    public function query(): DbalCartQuery
    {
        return new DbalCartQuery(
            $this->getContainer()->get('database_connection'),
            $this->getContainer()->get(Services::KERNEL_METADATA_ACCESS_REGISTRY)
        );
    }

    public function tearDown()
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $this->dropSchema($em);

        parent::tearDown();
    }
}
