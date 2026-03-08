<?php

namespace App\Services;

use App\Contracts\PublisherInterface;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Throwable;
class RabbitMQPublisher implements PublisherInterface
{
    public function publish(string $event, array $data): void
    {
        $config = config('services.rabbitmq');

        if (!($config['enabled'] ?? false)) {
            return;
        }

        $connection = null;
        $channel = null;

        try {
            $connection = new AMQPStreamConnection(
                $config['host'],
                $config['port'],
                $config['user'],
                $config['password'],
                $config['vhost']
            );

            $channel = $connection->channel();

            $exchange = $config['exchange'];
            $queue = $config['queue'];
            $routingKey = sprintf('%s.%s', $config['routing_prefix'], $event);

            $channel->exchange_declare($exchange, 'topic', false, true, false);
            $channel->queue_declare($queue, false, true, false, false);
            $channel->queue_bind($queue, $exchange, $config['routing_prefix'] . '.#');

            $payload = [
                'event' => $event,
                'occurred_at' => now()->toIso8601String(),
                'data' => $data,
            ];

            $message = new AMQPMessage(
                json_encode($payload, JSON_THROW_ON_ERROR),
                [
                    'content_type' => 'application/json',
                    'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                ]
            );

            $channel->basic_publish($message, $exchange, $routingKey);
        } catch (Throwable $exception) {
            Log::error('Failed to publish employee event to RabbitMQ.', [
                'event' => $event,
                'data' => $data,
                'message' => $exception->getMessage(),
            ]);
        } finally {
            try {
                if ($channel !== null && $channel->is_open()) {
                    $channel->close();
                }
            } catch (Throwable) {
            }

            try {
                if ($connection !== null && $connection->isConnected()) {
                    $connection->close();
                }
            } catch (Throwable) {
            }
        }
    }
}
