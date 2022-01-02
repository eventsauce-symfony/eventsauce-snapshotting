<?php

declare(strict_types=1);


namespace Andreo\EventSauce\Snapshotting;

use EventSauce\Clock\Clock;
use EventSauce\EventSourcing\ClassNameInflector;
use EventSauce\EventSourcing\Serialization\PayloadSerializer;

final class ConstructingSnapshotStateSerializer implements SnapshotStateSerializer
{
    public function __construct(
        private PayloadSerializer $payloadSerializer,
        private Clock $clock,
        private ClassNameInflector $classNameInflector
    ) {}

    public function serialize(SnapshotState $state): array
    {
        $state = $state->withCreatedAt($this->clock->now());
        $state = $state->withStateType($this->classNameInflector->instanceToType($state->state));

        $payload = $this->payloadSerializer->serializePayload($state->state);
        $headers = $state->headers;

        return [
            'headers' => $headers,
            'payload' => $payload,
        ];
    }

    public function unserialize(array $payload): SnapshotState
    {
        $className = $this->classNameInflector->typeToClassName($payload['headers'][Header::STATE_TYPE->value]);
        $event = $this->payloadSerializer->unserializePayload($className, $payload['payload']);

        return SnapshotState::from($event, $payload['headers']);
    }
}
