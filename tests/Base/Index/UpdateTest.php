<?php

declare(strict_types=1);

namespace Sigmie\Tests\Base\Index;

use Exception;
use Sigmie\Base\Analysis\Analyzer;
use Sigmie\Base\Analysis\CharFilter\PatternFilter;
use Sigmie\Base\Analysis\DefaultAnalyzer;
use Sigmie\Base\Analysis\Tokenizers\Pattern;
use Sigmie\Base\Analysis\Tokenizers\Whitespaces;
use Sigmie\Base\APIs\Calls\Index;
use Sigmie\Base\Documents\Document;
use Sigmie\Base\Documents\DocumentsCollection;
use Sigmie\Base\Index\AliasActions;
use Sigmie\Base\Index\Blueprint;
use Sigmie\Base\Mappings\Types\Date;
use Sigmie\Base\Mappings\Types\Text;
use Sigmie\Sigmie;
use Sigmie\Testing\ClearIndices;
use Sigmie\Testing\TestCase;
use Sigmie\Support\Update\Update as Update;

use function Sigmie\Helpers\name_configs;

class UpdateTest extends TestCase
{
    use Index, AliasActions;

    /**
     * @var Sigmie
     */
    private $sigmie;

    public function setUp(): void
    {
        parent::setUp();

        $this->sigmie = new Sigmie($this->httpConnection, $this->events);
    }

    /**
    * @test
    */
    public function foo()
    {
        //TODO Char filter 
        //TODO Tokenizer
        //TODO Default Analyzer
        //TODO Analyzer
        //TODO Config

        $this->assertTrue(false);
    }

    /**
     * @test
     */
    public function update_index_one_way_synonyms()
    {
        $this->sigmie->newIndex('foo')
            ->withoutMappings()
            ->oneWaySynonyms('bar_name', [
                'ipod' => ['i-pod', 'i pod']
            ])
            ->create();

        $oldData = $this->indexData('foo');

        $this->sigmie->index('foo')->update(function (Update $update) {

            $update->oneWaySynonyms('bar_name', [
                'mickey' => ['mouse', 'goofy'],
            ]);

            return $update;
        });

        $newData = $this->indexData('foo');

        $this->assertArrayHasKey('bar_name', $oldData['settings']['index']['analysis']['filter']);
        $this->assertEquals([
                'i-pod, i pod => ipod',
        ], $oldData['settings']['index']['analysis']['filter']['bar_name']['synonyms']);

        $this->assertArrayHasKey('bar_name', $newData['settings']['index']['analysis']['filter']);
        $this->assertEquals([
            'mouse, goofy => mickey',
        ], $newData['settings']['index']['analysis']['filter']['bar_name']['synonyms']);
    }

    /**
     * @test
     */
    public function update_index_stemming()
    {
        $this->sigmie->newIndex('foo')
            ->withoutMappings()
            ->stemming('bar_name', [
                'am' => ['be', 'are'],
                'mouse' => ['mice'],
                'feet' => ['foot'],
            ],)
            ->create();

        $oldData = $this->indexData('foo');

        $this->sigmie->index('foo')->update(function (Update $update) {

            $update->stemming('bar_name', [
                'mickey' => ['mouse', 'goofy'],
            ],);

            return $update;
        });

        $newData = $this->indexData('foo');

        $this->assertArrayHasKey('bar_name', $oldData['settings']['index']['analysis']['filter']);
        $this->assertEquals([
            'be, are => am',
            'mice => mouse',
            'foot => feet',
        ], $oldData['settings']['index']['analysis']['filter']['bar_name']['rules']);

        $this->assertArrayHasKey('bar_name', $newData['settings']['index']['analysis']['filter']);
        $this->assertEquals([
            'mouse, goofy => mickey',
        ], $newData['settings']['index']['analysis']['filter']['bar_name']['rules']);
    }

    /**
     * @test
     */
    public function update_index_synonyms()
    {
        $this->sigmie->newIndex('foo')
            ->withoutMappings()
            ->twoWaySynonyms('foo_two_way_synonyms', [
                ['treasure', 'gem', 'gold', 'price'],
                ['friend', 'buddy', 'partner']
            ])
            ->create();

        $oldData = $this->indexData('foo');

        $this->sigmie->index('foo')->update(function (Update $update) {

            $update->twoWaySynonyms('foo_two_way_synonyms', [['john', 'doe']]);

            return $update;
        });

        $newData = $this->indexData('foo');

        $this->assertArrayHasKey('foo_two_way_synonyms', $oldData['settings']['index']['analysis']['filter']);
        $this->assertEquals([
            'treasure, gem, gold, price',
            'friend, buddy, partner'
        ], $oldData['settings']['index']['analysis']['filter']['foo_two_way_synonyms']['synonyms']);

        $this->assertArrayHasKey('foo_two_way_synonyms', $newData['settings']['index']['analysis']['filter']);
        $this->assertEquals([
            'john, doe',
        ], $newData['settings']['index']['analysis']['filter']['foo_two_way_synonyms']['synonyms']);
    }

    /**
     * @test
     */
    public function update_index_stopwords()
    {
        $this->sigmie->newIndex('foo')
            ->withoutMappings()
            ->stopwords('foo_stopwords', ['foo', 'bar', 'baz'])
            ->create();

        $data = $this->indexData('foo');

        $this->assertArrayHasKey('foo_stopwords', $data['settings']['index']['analysis']['filter']);

        $this->sigmie->index('foo')->update(function (Update $update) {
            $update->stopwords('foo_stopwords', ['john', 'doe']);

            return $update;
        });

        $data = $this->indexData('foo');

        $this->assertArrayHasKey('foo_stopwords', $data['settings']['index']['analysis']['filter']);
        $this->assertEquals(['john', 'doe'], $data['settings']['index']['analysis']['filter']['foo_stopwords']['stopwords']);
    }

    /**
     * @test
     */
    public function exception_when_not_returned()
    {
        $this->expectException(Exception::class);

        $this->sigmie->newIndex('foo')
            ->withoutMappings()
            ->stopwords('foo_stopwords', ['foo', 'bar', 'baz'])
            ->create();

        $updatedIndex = $this->sigmie->index('foo')->update(function (Update $update) {
        });
    }

    /**
     * @test
     */
    public function mappings()
    {
        $this->sigmie->newIndex('foo')
            ->mappings(function (Blueprint $blueprint) {

                $blueprint->text('bar')->searchAsYouType();
                $blueprint->text('created_at')->unstructuredText();

                return $blueprint;
            })
            ->create();

        $index = $this->sigmie->index('foo');

        $props = $index->getMappings()->properties();

        $this->assertInstanceOf(Text::class, $props['created_at']);

        $updatedIndex = $index->update(function (Update $update) {

            $update->mappings(function (Blueprint $blueprint) {
                $blueprint->date('created_at');
                $blueprint->number('count')->float();

                return $blueprint;
            });

            return $update;
        });

        $props = $updatedIndex->getMappings()->properties();

        $this->assertInstanceOf(Date::class, $props['created_at']);
        $this->assertArrayHasKey('count', $props);
    }

    /**
     * @test
     */
    public function reindex_docs()
    {
        $this->sigmie->newIndex('foo')
            ->withoutMappings()
            ->create();

        $index = $this->sigmie->index('foo');
        $oldIndexName = $index->name();

        $docs = new DocumentsCollection();
        for ($i = 0; $i < 10; $i++) {
            $docs->addDocument(new Document(['foo' => 'bar']));
        }

        $index->addDocuments($docs);

        $this->assertCount(10, $index);

        $updatedIndex = $index->update(function (Update $update) {
            $update->replicas(3);
            return $update;
        });

        [$name, $config] = name_configs($updatedIndex->toRaw());

        $this->assertEquals(3, $config['settings']['index']['number_of_replicas']);
        $this->assertNotEquals($oldIndexName, $index->name());
        $this->assertCount(10, $index);
    }

    /**
     * @test
     */
    public function delete_old_index()
    {
        $this->sigmie->newIndex('foo')
            ->withoutMappings()
            ->create();

        $index = $this->sigmie->index('foo');

        $oldIndexName = $index->name();

        $index->update(function (Update $update) {
            return $update;
        });

        $this->assertNotEquals($oldIndexName, $index->name());
        $this->assertTrue($this->getIndices($oldIndexName)->isEmpty());
    }

    /**
     * @test
     */
    public function index_name()
    {
        $this->sigmie->newIndex('foo')
            ->withoutMappings()
            ->create();

        $index = $this->sigmie->index('foo');

        $oldIndexName = $index->name();

        $index->update(function (Update $update) {

            return $update;
        });

        $this->assertNotEquals($oldIndexName, $index->name());
    }

    /**
     * @test
     */
    public function index_shards_and_replicas()
    {
        $this->sigmie->newIndex('foo')
            ->withoutMappings()
            ->shards(1)
            ->replicas(1)
            ->create();

        $index = $this->sigmie->index('foo');

        [$name, $config] = name_configs($index->toRaw());

        $this->assertEquals(1, $config['settings']['index']['number_of_shards']);
        $this->assertEquals(1, $config['settings']['index']['number_of_replicas']);

        $index->update(function (Update $update) {

            $update->replicas(2)->shards(2);

            return $update;
        });

        [$name, $config] = name_configs($index->toRaw());

        $this->assertEquals(2, $config['settings']['index']['number_of_shards']);
        $this->assertEquals(2, $config['settings']['index']['number_of_replicas']);
    }

    private function indexData(string $name): array
    {
        $json = $this->indexAPICall($name, 'GET')->json();
        $indexName = array_key_first($json);

        return $json[$indexName];
    }
}