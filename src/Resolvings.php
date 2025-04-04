<?php

namespace Mehradsadeghi\FilterQueryString;

use Closure;

trait Resolvings {

    private function resolve($filterName, $value)
    {
        if($this->isCustomFilter($filterName)) {
            return $this->resolveCustomFilter($filterName, $value);
        }

        $availableFilter = $this->availableFilters[$filterName] ?? $this->availableFilters['default'];

        return app($availableFilter, ['filter' => $filterName, 'values' => $value]);
    }

    private function resolveCustomFilter($filterName, $value): Closure
    {
        return $this->getClosure($this->makeCallable($filterName), $value);
    }

    private function makeCallable($filter): string
    {
        return static::class.'@'.$filter;
    }

    private function isCustomFilter($filterName): bool
    {
        return method_exists($this, $filterName);
    }

    private function getClosure($callable, $value): Closure
    {
        return function ($query, $nextFilter) use ($callable, $value) {
            return app()->call($callable, ['query' => $nextFilter($query), 'value' => $value]);
        };
    }
}
