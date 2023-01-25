<?php

declare(strict_types=1);

namespace Andreo\EventSauce\Snapshotting\Serializer;

use Andreo\EventSauce\Snapshotting\Aggregate\SnapshotState;
use EventSauce\Clock\Clock;
use EventSauce\EventSourcing\ClassNameInflector;
use EventSauce\EventSourcing\Serialization\PayloadSerializer;

final readonly class ConstructingSnapshotStateSerializer implements SnapshotStateSerializer
{
    public function __construct(
        private PayloadSerializer $payloadSerializer,
        private Clock $clock,
        private ClassNameInflector $classNameInflector
    ) {
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function serialize(SnapshotState $state): array
    {
        $state = $state->withCreatedAt($this->clock->now());
        $state = $state->withStateType($this->classNameInflector->instanceToType($state->payload));

        $payload = $this->payloadSerializer->serializePayload($state->payload);
        $headers = $state->headers;

        return [
            'headers' => $headers,
            'payload' => $payload,
        ];
    }

    /**
     * @param array<string, array<mixed>> $payload
     */
    public function unserialize(array $payload): SnapshotState
    {
        /** @var string $stateType */
        $stateType = $payload['headers'][Header::STATE_TYPE->value];
        /** @var class-string $className */
        $className = $this->classNameInflector->typeToClassName($stateType);
        $event = $this->payloadSerializer->unserializePayload($className, $payload['payload']);

        /** @var array<string, int|string|array<mixed>|bool|float> $headers */
        $headers = $payload['headers'];

        return SnapshotState::create($event, $headers);
    }
}
