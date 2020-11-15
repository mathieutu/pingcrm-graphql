<?php

namespace App\Console\Commands;

use App\Http\Graphql\GraphQL;
use GraphQL\Type\Schema;
use GraphQL\Utils\SchemaPrinter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

class GenerateFragments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gql:gen-php';

    protected $description = 'Generate GQL fragments from php';
    private GraphQL $graphQL;

    public function __construct(GraphQL $graphQL)
    {
        parent::__construct();

        $this->graphQL = $graphQL;
    }

    public function handle(): int
    {
        foreach ($this->graphQL->generateFragmentsSDLFromPHPGenerators() as $fileName) {
            $this->info("$fileName was generated.");
        }

        return 0;
    }
}
