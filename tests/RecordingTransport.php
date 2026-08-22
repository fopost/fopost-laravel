<?php

declare(strict_types=1);

namespace Fopost\Laravel\Tests;

use Fopost\Sdk\Http\Response;
use Fopost\Sdk\Http\Transport;

/** Stands in for the wire: records what was asked for, replays a canned body. */
final class RecordingTransport implements Transport
{
    /** @var array<int, array{method: string, url: string, headers: array<string, string>, body: ?string}> */
    public array $calls = [];

    public function __construct(private readonly string $body = '{"data":[]}', private readonly int $status = 200)
    {
    }

    public function send(string $method, string $url, array $headers, ?string $body): Response
    {
        $this->calls[] = ['method' => $method, 'url' => $url, 'headers' => $headers, 'body' => $body];

        return new Response($this->status, ['content-type' => 'application/json'], $this->body);
    }
}
