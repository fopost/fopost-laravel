<?php

declare(strict_types=1);

namespace Fopost\Laravel\Tests;

use Fopost\Laravel\Facades\Fopost;
use Fopost\Sdk\Client;
use Fopost\Sdk\Resource\AccountsResource;
use Fopost\Sdk\Resource\AiResource;
use Fopost\Sdk\Resource\LabelsResource;
use Fopost\Sdk\Resource\PostsResource;
use Fopost\Sdk\Resource\WorkspacesResource;

final class FacadeTest extends TestCase
{
    public function test_it_resolves_the_container_client(): void
    {
        $this->assertSame($this->app->make(Client::class), Fopost::getFacadeRoot());
    }

    public function test_it_exposes_every_resource(): void
    {
        $this->assertInstanceOf(PostsResource::class, Fopost::posts());
        $this->assertInstanceOf(AccountsResource::class, Fopost::accounts());
        $this->assertInstanceOf(WorkspacesResource::class, Fopost::workspaces());
        $this->assertInstanceOf(LabelsResource::class, Fopost::labels());
        $this->assertInstanceOf(AiResource::class, Fopost::ai());
    }

    public function test_it_proxies_a_call_through_to_the_client(): void
    {
        $transport = new RecordingTransport('{"data":[{"id":"ws_1","name":"Studio"}]}');
        $this->app->instance(Client::class, new Client('fp_stub', 'https://api.example.test', 30.0, 3, $transport));

        $workspaces = Fopost::workspaces()->list();

        $this->assertCount(1, $transport->calls);
        $this->assertSame('GET', $transport->calls[0]['method']);
        $this->assertSame('https://api.example.test/api/v1/workspaces', $transport->calls[0]['url']);
        $this->assertSame('fp_stub', $transport->calls[0]['headers']['X-API-Key']);
        $this->assertSame('ws_1', $workspaces[0]->id);
    }
}
