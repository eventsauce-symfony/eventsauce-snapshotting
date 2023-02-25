<?php

declare(strict_types=1);

namespace Andreo\EventSauce\Snapshotting\Tests\Conditional;

use Andreo\EventSauce\Aggregate\Tests\DummyAggregateId;
use Andreo\EventSauce\Snapshotting\Conditional\AggregateRootRepositoryWithConditionalSnapshot;
use Andreo\EventSauce\Snapshotting\Conditional\EveryNEventConditionalSnapshotStrategy;
use Andreo\EventSauce\Snapshotting\Tests\Conditional\Doubles\ConditionalAggregateFake;
use Andreo\EventSauce\Snapshotting\Tests\Conditional\Doubles\NumberFakeIncremented;
use Andreo\EventSauce\Snapshotting\Tests\Doubles\AggregateIdFake;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\AggregateRootRepository;
use EventSauce\EventSourcing\EventSourcedAggregateRootRepository;
use EventSauce\EventSourcing\MessageDecorator;
use EventSauce\EventSourcing\MessageDispatcher;
use EventSauce\EventSourcing\MessageRepository;
use EventSauce\EventSourcing\Snapshotting\ConstructingAggregateRootRepositoryWithSnapshotting;
use EventSauce\EventSourcing\Snapshotting\InMemorySnapshotRepository;
use EventSauce\EventSourcing\TestUtilities\AggregateRootTestCase;

final class ConditionalSnapshotStoreStrategyTest extends AggregateRootTestCase
{
    protected InMemorySnapshotRepository $snapshotRepository;

    /**
     * @var AggregateRootRepositoryWithConditionalSnapshot&AggregateRootRepository
     */
    protected AggregateRootRepository $repository;

    /**
     * @test
     */
    public function should_not_store_snapshot_if_number_of_event_is_less_than_declared(): void
    {
        $this->given(
            new NumberFakeIncremented(1),
            new NumberFakeIncremented(2),
        );

        /** @var ConditionalAggregateFake $aggregate */
        $aggregate = $this->repository->retrieveFromSnapshot($this->aggregateRootId);
        $this->assertInstanceOf(ConditionalAggregateFake::class, $aggregate);

        $this->repository->persist($aggregate);
        $this->repository->storeSnapshot($aggregate);

        $snapshot = $this->snapshotRepository->retrieve($this->aggregateRootId);
        $this->assertNull($snapshot);

        $this->messageRepository->purgeLastCommit();
    }

    /**
     * @test
     */
    public function should_store_snapshot_if_number_of_event_is_equals_declared(): void
    {
        $this->given(
            new NumberFakeIncremented(1),
            new NumberFakeIncremented(2),
            new NumberFakeIncremented(3),
        );

        /** @var ConditionalAggregateFake $aggregate */
        $aggregate = $this->repository->retrieveFromSnapshot($this->aggregateRootId);
        $this->assertInstanceOf(ConditionalAggregateFake::class, $aggregate);

        $this->repository->persist($aggregate);
        $this->repository->storeSnapshot($aggregate);

        $snapshot = $this->snapshotRepository->retrieve($this->aggregateRootId);
        $this->assertEquals(3, $snapshot->state());

        $aggregate->increment();
        $aggregate->increment();
        $this->repository->persist($aggregate);
        $this->repository->storeSnapshot($aggregate);

        $snapshot = $this->snapshotRepository->retrieve($this->aggregateRootId);
        $this->assertEquals(3, $snapshot->state());

        /** @var ConditionalAggregateFake $aggregate */
        $aggregate = $this->repository->retrieveFromSnapshot($this->aggregateRootId);
        $this->assertInstanceOf(ConditionalAggregateFake::class, $aggregate);
        $this->assertEquals(5, $aggregate->getIncrementedNumber());

        $this->messageRepository->purgeLastCommit();
    }

    protected function newAggregateRootId(): AggregateRootId
    {
        return AggregateIdFake::create();
    }

    protected function aggregateRootClassName(): string
    {
        return ConditionalAggregateFake::class;
    }

    protected function aggregateRootRepository(
        string $className,
        MessageRepository $repository,
        MessageDispatcher $dispatcher,
        MessageDecorator $decorator
    ): AggregateRootRepository {
        $this->snapshotRepository = new InMemorySnapshotRepository();

        $regularRepository = new ConstructingAggregateRootRepositoryWithSnapshotting(
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

        return new AggregateRootRepositoryWithConditionalSnapshot(
            $regularRepository,
            new EveryNEventConditionalSnapshotStrategy(3)
        );
    }
}
