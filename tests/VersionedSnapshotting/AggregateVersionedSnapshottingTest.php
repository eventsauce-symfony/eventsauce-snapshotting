<?php

declare(strict_types=1);

namespace Tests\VersionedSnapshotting;

use Andreo\EventSauce\Snapshotting\AggregateRootRepositoryWithVersionedSnapshotting;
use Andreo\EventSauce\Snapshotting\SnapshotState;
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
    protected InMemorySnapshotRepository $snapshotRepository;

    /**
     * @var AggregateRootRepositoryWithVersionedSnapshotting
     */
    protected AggregateRootRepository $repository;

    /**
     * @test
     */
    public function should_filter_out_outdated_snapshot(): void
    {
        $this->repository->storeSnapshot(DeprecatedAggregateFake::create($this->aggregateRootId));
        $snapshot = $this->snapshotRepository->retrieve($this->aggregateRootId);
        /** @var SnapshotState $snapshotState */
        $snapshotState = $snapshot->state();
        /** @var DeprecatedStateStub $state */
        $state = $snapshotState->state;
        $this->assertInstanceOf(DeprecatedStateStub::class, $state);
        $this->assertEquals(1, $snapshotState->schemaVersion());
        $this->assertEquals('deprecated', $state->value);

        /** @var AggregateFake $aggregate */
        $aggregate = $this->repository->retrieveFromSnapshot($this->aggregateRootId);
        $this->assertInstanceOf(AggregateFake::class, $aggregate);
        $this->assertEquals(2, $aggregate::getSnapshotVersion());
        $this->assertEquals('new', $aggregate->value);

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
