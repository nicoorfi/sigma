<?php

declare(strict_types=1);

namespace Sigmie\Testing;

use Sigmie\Base\Index\Actions;
use Sigmie\Sigmie;

class TestCase extends \PHPUnit\Framework\TestCase
{
    use Testing, Actions, Assertions, TestIndex, ClearIndices;

    protected Sigmie $sigmie;

    public function setUp(): void
    {
        parent::setUp();

        $this->setupTestConnection();

        if (is_null(getenv('PARATEST'))) {
            $this->clearIndices();
        }

        $this->createTestIndex();

        $this->sigmie = new Sigmie($this->httpConnection);
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }
}
