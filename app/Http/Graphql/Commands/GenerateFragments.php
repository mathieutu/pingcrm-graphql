<?php

namespace App\Http\Graphql\Commands;

use App\Http\Graphql\GraphQL;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

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
        foreach ($this->graphQL->getFragmentsSDLFromPHPGenerators() as $filePath => $content) {
            File::put($filePath, $content);
            $this->info("$filePath was generated.");
        }

        return 0;
    }
}
