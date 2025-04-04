<?php

namespace Mehradsadeghi\FilterQueryString;

use Illuminate\Pipeline\Pipeline;
use Mehradsadeghi\FilterQueryString\Filters\{BaseClause, OrderbyClause, WhereClause, WhereInClause, WhereLikeClause};
use Mehradsadeghi\FilterQueryString\Filters\ComparisonClauses\{GreaterOrEqualTo, GreaterThan, LessOrEqualTo, LessThan};
use Mehradsadeghi\FilterQueryString\Filters\ComparisonClauses\Between\{Between, NotBetween};

trait FilterQueryString {

    use Resolvings;

    /**
     * @var array<string, class-string<BaseClause>>
     */
    private array $availableFilters = [
        'default' => WhereClause::class,
        'sort' => OrderbyClause::class,
        'greater' => GreaterThan::class,
        'greater_or_equal' => GreaterOrEqualTo::class,
        'less' => LessThan::class,
        'less_or_equal' => LessOrEqualTo::class,
        'between' => Between::class,
        'not_between' => NotBetween::class,
        'in' => WhereInClause::class,
        'like' => WhereLikeClause::class,
    ];

    public function scopeFilter($query, ...$filters)
    {
        $filters = collect($this->getFilters($filters))->map(
            fn(string|array $value, string $filter) => $this->resolve($filter, $this->unserializeValue($value))
        )->toArray();

        return app(Pipeline::class)
            ->send($query)
            ->through($filters)
            ->thenReturn();
    }

    private function getFilters($filters): array
    {
        $filter = function ($key) use ($filters) {
            $filters = $filters ?: $this->filters ?: [];

            return $this->unguardFilters || in_array($key, $filters);
        };

        return array_filter(request()->query(), $filter, ARRAY_FILTER_USE_KEY) ?? [];
    }

    private function unserializeValue(string|array $value): string|array|bool|null
    {
        if (filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
            return (bool) $value;
        }

        if ($value === 'null') {
            return null;
        }

        return $value;
    }
}
