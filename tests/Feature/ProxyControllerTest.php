<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Cluster\TokenController;
use App\Models\Cluster;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProxyControllerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var Cluster
     */
    private $cluster;

    /**
     * @var string
     */
    private $adminToken;

    /**
     * @var string
     */
    private $searchToken;

    public function setUp(): void
    {
        parent::setUp();

        $this->cluster = Cluster::factory()->create();

        $this->adminToken = $this->cluster->createToken(TokenController::ADMIN, ['*'])->plainTextToken;
        $this->searchToken = $this->cluster->createToken(TokenController::SEARCH_ONLY, ['search'])->plainTextToken;
    }

    /**
     * @test
     */
    public function proxy_returns_json_response()
    {
        $this->get(route('proxy'), ['Authorization' => "Bearer {$this->adminToken}"])
            ->assertJson([
                "tagline" => "You Know, for Search"
            ]);
    }

    /**
     * @test
     */
    public function proxy_returns_login_without_token()
    {
        $this->get(route('proxy'), ['Authorization' => ""])->assertRedirect('https://localhost/login');
    }

    /**
     * @test
     */
    public function request_path_forwarding()
    {
        $this->get(route('proxy', ['endpoint' => '/_cluster/health']), ['Authorization' => "Bearer {$this->adminToken}"])->assertJson(['number_of_nodes' => 1]);
    }

    /**
     * @test
     */
    public function create_index()
    {
        // Delete if already exists to avoid duplicate index error
        $this->delete(route('proxy', ['endpoint' => 'my-index']), [], ['Authorization' => "Bearer {$this->adminToken}"]);

        $this->put(route('proxy', ['endpoint' => 'my-index']), [], ['Authorization' => "Bearer {$this->adminToken}"])->assertJson(["acknowledged" => true]);
    }

    /**
     * @test
     */
    public function dont_throw_on_http_error()
    {
        $this->put(route('proxy', ['endpoint' => 'duplicate-index']), [], ['Authorization' => "Bearer {$this->adminToken}"]);

        $this->put(route('proxy', ['endpoint' => 'duplicate-index']), [], ['Authorization' => "Bearer {$this->adminToken}"])->assertJson(['status' => 400]);
    }

    /**
     * @test
     */
    public function crete_doc()
    {
        $this->put(route('proxy', ['endpoint' => 'my-index']), [], ['Authorization' => "Bearer {$this->adminToken}"]);

        $response = $this->withHeaders(['Authorization' => "Bearer {$this->adminToken}", 'Content-Type' => 'application/json'])
            ->json(
                'POST',
                route('proxy', ['endpoint' => 'my-index/_doc']),
                [
                    "timestamp" => "2099-11-15T13:12:00",
                    "user" => [
                        "id" => "kimchy"
                    ]
                ],
            );

        $response->assertJson(["result" => "created"]);
    }
}
