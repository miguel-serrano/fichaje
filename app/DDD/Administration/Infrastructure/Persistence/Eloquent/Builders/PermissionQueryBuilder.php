<?php

namespace App\DDD\Administration\Infrastructure\Persistence\Eloquent\Builders;

use App\DDD\Administration\Domain\ValueObjects\BoundedContext;
use App\DDD\Administration\Domain\ValueObjects\PermissionId;
use App\DDD\Administration\Domain\ValueObjects\PermissionSlug;
use Illuminate\Database\Query\Builder;

class PermissionQueryBuilder extends Builder
{
    public function wherePermissionId(PermissionId $id): self
    {
        return $this->where('id', $id->value());
    }

    public function whereSlug(PermissionSlug $slug): self
    {
        return $this->where('slug', $slug->value());
    }

    public function whereBoundedContext(BoundedContext $context): self
    {
        return $this->where('bounded_context', $context->value);
    }

    public function orderByDefault(): self
    {
        return $this->orderBy('bounded_context')->orderBy('slug');
    }
}
