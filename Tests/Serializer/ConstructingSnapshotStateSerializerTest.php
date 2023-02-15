<?php

declare(strict_types=1);

namespace Andreo\EventSauce\Snapshotting\Tests\Serializer;

use Andreo\EventSauce\Snapshotting\Aggregate\SnapshotState;
use Andreo\EventSauce\Snapshotting\Serializer\ConstructingSnapshotStateSerializer;
use Andreo\EventSauce\Snapshotting\Serializer\Header;
use Andreo\EventSauce\Snapshotting\Tests\Serializer\Doubles\StateFake;
use EventSauce\Clock\SystemClock;
use EventSauce\EventSourcing\DotSeparatedSnakeCaseInflector;
use EventSauce\EventSourcing\Serialization\ConstructingPayloadSerializer;
use PHPUnit\Framework\TestCase;

final class ConstructingSnapshotStateSerializerTest extends TestCase
{
    /**
     * @test
     */
    public function should_serialize_and_unserialize(): void
    {
        $dummyState = new StateFake('foo', 'bar');
        $snapshotState = SnapshotState::create($dummyState);

        $serializer = $this->serializer();
        $payload = $serializer->serialize($snapshotState);

        $this->assertArrayHasKey('payload', $payload);
        $this->assertArrayHasKey('foo', $payload['payload']);
        $this->assertArrayHasKey('bar', $payload['payload']);

        $this->assertArrayHasKey('headers', $payload);
        $this->assertArrayHasKey(Header::CREATED_AT->value, $payload['headers']);
        $this->assertArrayHasKey(Header::STATE_TYPE->value, $payload['headers']);

        $state = $serializer->unserialize($payload);
        $this->assertTrue(property_exists($state, 'payload'));
        $this->assertTrue(property_exists($state, 'headers'));
        $this->assertInstanceOf(StateFake::class, $state->payload);
        $this->assertTrue(property_exists($state->payload, 'foo'));
        $this->assertTrue(property_exists($state->payload, 'bar'));

        $this->assertArrayHasKey(Header::CREATED_AT->value, $state->headers);
        $this->assertArrayHasKey(Header::STATE_TYPE->value, $state->headers);
        $this->assertSame(1, $state->payload::getSnapshotVersion());
    }

    public function serializer(): ConstructingSnapshotStateSerializer
    {
        return new ConstructingSnapshotStateSerializer(
            new ConstructingPayloadSerializer(),
            new SystemClock(),
            new DotSeparatedSnakeCaseInflector()
        );
    }
}
