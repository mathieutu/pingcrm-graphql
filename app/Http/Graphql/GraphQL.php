<?php


namespace App\Http\Graphql;


use Carbon\Carbon;
use GraphQL\Error\InvariantViolation;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\ScalarTypeDefinitionNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\CustomScalarType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use GraphQL\Utils\SchemaExtender;
use GraphQL\Utils\SchemaPrinter;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

class GraphQL
{
    public function getSchema(): Schema
    {
        // decorateTypeConfig à rapatrier si besoin d'interface

        $initialSchema = BuildSchema::build(File::get($this->getSchemaPath()), fn(...$args) => $this->decorateTypeConfig(...$args));

        return SchemaExtender::extend($initialSchema, Parser::parse($this->getFragmentsSDL()));
    }

    public function getSchemaPath(): string
    {
        return $this->getSchemaFolder('schema.graphql');
    }

    public function getSchemaFolder($path = ''): string
    {
        return __DIR__ . '/Schema' . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    public function getFragmentsSDL(): string
    {
        return collect(File::allFiles($this->getFragmentsFolderPath()))
            ->filter(fn(SplFileInfo $file) => in_array($file->getExtension(), ['graphql', 'gql']))
            ->map(fn(SplFileInfo $file) => $file->getContents())
            ->join('');
    }

    public function getFragmentsFolderPath($path = ''): string
    {
        return $this->getSchemaFolder('fragments') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    public function generateFragmentsSDLFromPHPGenerators(): array
    {
        $namespace = app()->getNamespace();

        return collect(File::allFiles($this->getGeneratorsFolderPath()))
            ->filter(fn(SplFileInfo $file) => $file->getExtension() === 'php')
            ->map(function (SplFileInfo $file) use ($namespace) {
                $class = $namespace . str_replace(
                        ['/', '.php'],
                        ['\\', ''],
                        Str::after($file->getRealPath(), realpath(app_path()) . DIRECTORY_SEPARATOR)
                    );

                $types = app()->make($class)->__invoke();

                $content = collect($types)
                    ->map(fn(Type $type) => SchemaPrinter::printType($type))
                    ->join("\n\n");

                $fileName = '_' . Str::snake($file->getFilenameWithoutExtension()) . '.graphql';

                File::put(
                    $this->getFragmentsFolderPath($fileName),
                    "# Generated content from {$file->getFilename()}. DO NOT EDIT. \n\n{$content}\n"
                );

                return $fileName;
            })
            ->all();
    }

    public function getGeneratorsFolderPath(): string
    {
        return $this->getSchemaFolder('Generators');
    }


    public function resolveClassName(string $name): string
    {
        return __NAMESPACE__ . '\\Resolvers\\' . Str::studly($name);
    }

    private function decorateTypeConfig(array $config, TypeDefinitionNode $astNode): array
    {
        if ($astNode instanceof ScalarTypeDefinitionNode) {
            $config = $this->resolveScalar($config);
        }

        return $config;
    }

    private function resolveScalar(array $config): array
    {
        $className = $this->resolveClassName($config['name']);

        if (class_exists($className) && method_exists($className, 'serialize') && method_exists($className, 'parse')) {
            $resolver = app($className);

            return array_merge($config, [
                'serialize' => fn($value) => $resolver->serialize($value),
                'parseValue' => fn($value) => $resolver->parse($value),
                'parseLiteral' => fn(Node $ast) => $resolver->parse($ast->value),
            ]);
        }

        throw new InvariantViolation("Custom scalar {$config['name']} must have corresponding handler class $className with serialize and parse methods.");
    }
}
