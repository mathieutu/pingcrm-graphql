<?php

namespace App\Console\Commands;

use App\Http\Graphql\GraphQL;
use GraphQL\Utils\SchemaPrinter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateSchema extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gql:schema {--write}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    private GraphQL $graphQL;

    public function __construct(GraphQL $graphQL)
    {
        parent::__construct();

        $this->graphQL = $graphQL;
    }

    public function handle(): int
    {
        $fileName = '_schema.graphql';

        $schema = $this->graphQL->getSchema();

        if ($this->hasOption('write')) {
            File::put($this->graphQL->getSchemaFolder($fileName), SchemaPrinter::doPrint($schema));
        }

        $schema->assertValid();
        $this->info('Schema valid!');

        return 0;
    }
}
