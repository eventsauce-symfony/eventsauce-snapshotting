<?php

declare(strict_types=1);

namespace Andreo\EventSauce\Snapshotting\Tests\Versioned;

use Andreo\EventSauce\Snapshotting\Tests\Doubles\AggregateIdFake;
use Andreo\EventSauce\Snapshotting\Tests\Versioned\Doubles\DeprecatedAggregateFake;
use Andreo\EventSauce\Snapshotting\Tests\Versioned\Doubles\NewAggregateFake;
use Andreo\EventSauce\Snapshotting\Versioned\AggregateRootRepositoryWithVersionedSnapshotting;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\AggregateRootRepository;
use EventSauce\EventSourcing\EventSourcedAggregateRootRepository;
use EventSauce\EventSourcing\MessageDecorator;
use EventSauce\EventSourcing\MessageDispatcher;
use EventSauce\EventSourcing\MessageRepository;
use EventSauce\EventSourcing\Snapshotting\InMemorySnapshotRepository;
use EventSauce\EventSourcing\TestUtilities\AggregateRootTestCase;

final class AggregateVersionedSnapshottingTest extends AggregateRootTestCase
{
    protected ?InMemorySnapshotRepository $snapshotRepository = null;

    /**
     * @var AggregateRootRepositoryWithVersionedSnapshotting
     */
    protected AggregateRootRepository $repository;

    private string $className = DeprecatedAggregateFake::class;

    /**
     * @test
     */
    public function should_filter_out_outdated_snapshot(): void
    {
        $this->repository->storeSnapshot(DeprecatedAggregateFake::create($this->aggregateRootId));
        /** @var NewAggregateFake $aggregate */
        $aggregate = $this->repository->retrieveFromSnapshot($this->aggregateRootId);
        $this->assertInstanceOf(DeprecatedAggregateFake::class, $aggregate);
        $this->assertEquals('deprecated', $aggregate->value);

        $this->className = NewAggregateFake::class;
        $this->setUpEventSauce();

        /** @var NewAggregateFake $aggregate */
        $aggregate = $this->repository->retrieveFromSnapshot($this->aggregateRootId);
        $this->assertInstanceOf(NewAggregateFake::class, $aggregate);
        $this->assertEquals('new', $aggregate->value);
    }

    protected function newAggregateRootId(): AggregateRootId
    {
        return AggregateIdFake::fromString('foo-uuid');
    }

    protected function aggregateRootClassName(): string
    {
        return $this->className;
    }

    protected function aggregateRootRepository(
        string $className,
        MessageRepository $repository,
        MessageDispatcher $dispatcher,
        MessageDecorator $decorator
    ): AggregateRootRepository {

        $this->snapshotRepository = $this->snapshotRepository ?? new InMemorySnapshotRepository();

        return new AggregateRootRepositoryWithVersionedSnapshotting(
            $className,
            $repository,
            $this->snapshotRepository,
            new EventSourcedAggregateRootRepository(
                $className,
                $repository,
                $dispatcher,
                $decorator
            )
        );
    }
}
