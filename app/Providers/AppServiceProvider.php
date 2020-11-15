<?php

namespace App\Providers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\UrlWindow;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use League\Glide\Server;

class AppServiceProvider extends ServiceProvider
{

    public function boot()
    {
        if ($this->app->runningInConsole() && !$this->app->runningUnitTests()) {
            $this->dumpAllSqlQueries();
        }
    }

    private function dumpAllSqlQueries()
    {
        $this->app['db']->listen(function (QueryExecuted $query) {
            if (strpos($query->sql, 'telescope_entries') === false) {
                dump($this->replaceBindings($query->bindings, $query->sql));
            }
        });
    }


    private function replaceBindings(array $bindings, string $query)
    {
        $search = '#BINDING#' . Str::random() . '#BINDING#';

        $queryWithHash = preg_replace('/\B\?/', $search, $query);

        $segments = explode($search, $queryWithHash);

        $result = array_shift($segments);

        foreach ($segments as $key => $segment) {
            $result .= str_replace('"', "'", json_encode($bindings[$key])) . $segment;
        }

        return $result;
    }


    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerGlide();
        $this->registerLengthAwarePaginator();
    }

    protected function registerGlide()
    {
        $this->app->bind(Server::class, function ($app) {
            return Server::create([
                'source' => Storage::getDriver(),
                'cache' => Storage::getDriver(),
                'cache_folder' => '.glide-cache',
                'base_url' => 'img',
            ]);
        });
    }

    protected function registerLengthAwarePaginator()
    {
        $this->app->bind(LengthAwarePaginator::class, function ($app, $values) {
            return new class(...array_values($values)) extends LengthAwarePaginator {
                public function only(...$attributes)
                {
                    return $this->transform(function ($item) use ($attributes) {
                        return $item->only($attributes);
                    });
                }

                public function transform($callback)
                {
                    $this->items->transform($callback);

                    return $this;
                }

                public function toArray()
                {
                    return [
                        'data' => $this->items->toArray(),
                        'links' => $this->links(),
                    ];
                }

                public function links($view = null, $data = [])
                {
                    $this->appends(Request::all());

                    $window = UrlWindow::make($this);

                    $elements = array_filter([
                        $window['first'],
                        is_array($window['slider']) ? '...' : null,
                        $window['slider'],
                        is_array($window['last']) ? '...' : null,
                        $window['last'],
                    ]);

                    return Collection::make($elements)->flatMap(function ($item) {
                        if (is_array($item)) {
                            return Collection::make($item)->map(function ($url, $page) {
                                return [
                                    'url' => $url,
                                    'label' => $page,
                                    'active' => $this->currentPage() === $page,
                                ];
                            });
                        } else {
                            return [
                                [
                                    'url' => null,
                                    'label' => '...',
                                    'active' => false,
                                ],
                            ];
                        }
                    })->prepend([
                        'url' => $this->previousPageUrl(),
                        'label' => 'Previous',
                        'active' => false,
                    ])->push([
                        'url' => $this->nextPageUrl(),
                        'label' => 'Next',
                        'active' => false,
                    ]);
                }
            };
        });
    }
}
