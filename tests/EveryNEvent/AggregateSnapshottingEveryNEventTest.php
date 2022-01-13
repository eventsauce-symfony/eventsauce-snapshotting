<?php

declare(strict_types=1);

namespace Tests\EveryNEvent;

use Andreo\EventSauce\Snapshotting\AggregateRootRepositoryWithSnapshottingAndStoreStrategy;
use Andreo\EventSauce\Snapshotting\EveryNEventCanStoreSnapshotStrategy;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\AggregateRootRepository;
use EventSauce\EventSourcing\EventSourcedAggregateRootRepository;
use EventSauce\EventSourcing\MessageDecorator;
use EventSauce\EventSourcing\MessageDispatcher;
use EventSauce\EventSourcing\MessageRepository;
use EventSauce\EventSourcing\Snapshotting\ConstructingAggregateRootRepositoryWithSnapshotting;
use EventSauce\EventSourcing\Snapshotting\InMemorySnapshotRepository;
use EventSauce\EventSourcing\TestUtilities\AggregateRootTestCase;

final class AggregateSnapshottingEveryNEventTest extends AggregateRootTestCase
{
    protected InMemorySnapshotRepository $snapshotRepository;

    private const EVERY_N_EVENT = 3;

    /**
     * @var AggregateRootRepositoryWithSnapshottingAndStoreStrategy
     */
    protected AggregateRootRepository $repository;

    /**
     * @test
     */
    public function snapshot_is_not_stored_if_number_of_events_is_not_match(): void
    {
        $this->given(
            new AggregateNumberIncremented(1),
            new AggregateNumberIncremented(2),
        );

        /** @var AggregateFake $aggregate */
        $aggregate = $this->repository->retrieveFromSnapshot($this->aggregateRootId);
        $this->assertInstanceOf(AggregateFake::class, $aggregate);

        $this->repository->persist($aggregate);
        $this->repository->storeSnapshot($aggregate);

        $snapshot = $this->snapshotRepository->retrieve($this->aggregateRootId);
        $this->assertNull($snapshot);

        $this->messageRepository->purgeLastCommit();
    }

    /**
     * @test
     */
    public function snapshot_is_stored_if_number_of_events_is_match(): void
    {
        $this->given(
            new AggregateNumberIncremented(1),
            new AggregateNumberIncremented(2),
            new AggregateNumberIncremented(3),
        );

        /** @var AggregateFake $aggregate */
        $aggregate = $this->repository->retrieveFromSnapshot($this->aggregateRootId);
        $this->assertInstanceOf(AggregateFake::class, $aggregate);

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

        /** @var AggregateFake $aggregate */
        $aggregate = $this->repository->retrieveFromSnapshot($this->aggregateRootId);
        $this->assertInstanceOf(AggregateFake::class, $aggregate);
        $this->assertEquals(5, $aggregate->getIncrementedNumber());

        $this->messageRepository->purgeLastCommit();
    }

    protected function newAggregateRootId(): AggregateRootId
    {
        return DummyAggregateId::create();
    }

    protected function aggregateRootClassName(): string
    {
        return AggregateFake::class;
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

        return new AggregateRootRepositoryWithSnapshottingAndStoreStrategy(
            $regularRepository,
            new EveryNEventCanStoreSnapshotStrategy(self::EVERY_N_EVENT)
        );
    }
}
