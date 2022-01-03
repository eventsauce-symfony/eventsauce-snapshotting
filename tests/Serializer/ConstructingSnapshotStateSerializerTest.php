<?php

declare(strict_types=1);

namespace Tests\Serializer;

use Andreo\EventSauce\Snapshotting\ConstructingSnapshotStateSerializer;
use Andreo\EventSauce\Snapshotting\Header;
use Andreo\EventSauce\Snapshotting\SnapshotState;
use Andreo\EventSauce\Snapshotting\SnapshotStateSerializer;
use EventSauce\Clock\SystemClock;
use EventSauce\EventSourcing\DotSeparatedSnakeCaseInflector;
use EventSauce\EventSourcing\Serialization\ConstructingPayloadSerializer;
use PHPUnit\Framework\TestCase;

final class ConstructingSnapshotStateSerializerTest extends TestCase
{
    /**
     * @test
     */
    public function serialize_state_works(): void
    {
        $dummyState = new StateStub('foo', 'bar');
        $snapshotState = SnapshotState::from($dummyState);

        $serializer = $this->serializer();
        $payload = $serializer->serialize($snapshotState);

        $this->assertArrayHasKey('payload', $payload);
        $this->assertArrayHasKey('foo', $payload['payload']);
        $this->assertArrayHasKey('bar', $payload['payload']);

        $this->assertArrayHasKey('headers', $payload);
        $this->assertArrayHasKey(Header::CREATED_AT->value, $payload['headers']);
        $this->assertArrayHasKey(Header::STATE_TYPE->value, $payload['headers']);

        $state = $serializer->unserialize($payload);
        $this->assertObjectHasAttribute('state', $state);
        $this->assertInstanceOf(StateStub::class, $state->state);
        $this->assertObjectHasAttribute('foo', $state->state);
        $this->assertObjectHasAttribute('bar', $state->state);
        $this->assertObjectHasAttribute('headers', $state);
        $this->assertArrayHasKey(Header::CREATED_AT->value, $state->headers);
        $this->assertArrayHasKey(Header::STATE_TYPE->value, $state->headers);
    }

    public function serializer(): SnapshotStateSerializer
    {
        return new ConstructingSnapshotStateSerializer(
            new ConstructingPayloadSerializer(),
            new SystemClock(),
            new DotSeparatedSnakeCaseInflector()
        );
    }
}