<?php

declare(strict_types=1);

namespace ModusDigital\LivewireDatatables\Filters;

use Illuminate\Database\Eloquent\Builder;

abstract class Filter
{
    protected string $name;

    protected string $field;

    protected mixed $default = null;

    protected ?string $placeholder = null;

    protected ?bool $hideLabel = null;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->field = strtolower(str_replace(' ', '_', $name));
    }

    final public static function make(string $name): static
    {
        return new static($name);
    }

    public function field(string $field): static
    {
        $this->field = $field;

        return $this;
    }

    public function default(mixed $default): static
    {
        $this->default = $default;

        return $this;
    }

    public function placeholder(string $placeholder): static
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function hideLabel(): static
    {
        $this->hideLabel = true;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getDefault(): mixed
    {
        return $this->default;
    }

    public function getPlaceholder(): ?string
    {
        return $this->placeholder;
    }

    public function shouldHideLabel(): bool
    {
        if ($this->hideLabel === null) {
            $this->hideLabel = ! (bool) config('livewire-datatables.filters.labels', false);
        }

        return $this->hideLabel;
    }

    /**
     * Get the attribute filtering details.
     * Override in child classes that support attribute filtering.
     *
     * @return array<string, mixed>
     */
    public function getAttributeFilterDetails(): array
    {
        return [];
    }

    /**
     * Check if current filtering requires attribute-based filtering.
     * Override in child classes that support attribute filtering.
     */
    public function requiresAttributeFiltering(): bool
    {
        return false;
    }

    /**
     * Enhanced method to check if a field is a model attribute.
     * Uses the same logic as HasColumns trait for consistency.
     */
    protected function isFieldAttribute(\Illuminate\Database\Eloquent\Model $model, string $field): bool
    {
        // Check if it's an accessor method (old Laravel syntax)
        $accessorMethod = 'get' . \Illuminate\Support\Str::studly($field) . 'Attribute';
        if (method_exists($model, $accessorMethod)) {
            return true;
        }

        // Check if it's defined in the model's $appends array
        if (in_array($field, $model->getAppends())) {
            return true;
        }

        // Check if it's a cast attribute but NOT a database column
        if (array_key_exists($field, $model->getCasts())) {
            try {
                if (! $this->isDatabaseColumn($model, $field)) {
                    return true;
                }
            } catch (\Throwable $e) {
                // If we can't determine database columns, assume it's an attribute if it's cast
                return true;
            }
        }

        // Check if it's a Laravel 9+ Attribute (new syntax)
        if (method_exists($model, $field)) {
            try {
                $reflection = new \ReflectionClass($model);
                if ($reflection->hasMethod($field)) {
                    $method = $reflection->getMethod($field);
                    $returnType = $method->getReturnType();

                    if ($returnType instanceof \ReflectionNamedType &&
                        $returnType->getName() === 'Illuminate\Database\Eloquent\Casts\Attribute') {
                        return true;
                    }
                }
            } catch (\ReflectionException $e) {
                // Log the error but don't fail
                \Illuminate\Support\Facades\Log::debug('Reflection error in filter attribute detection', [
                    'model' => get_class($model),
                    'field' => $field,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return false;
    }

    /**
     * Check if a field is a database column.
     */
    protected function isDatabaseColumn(\Illuminate\Database\Eloquent\Model $model, string $field): bool
    {
        try {
            $connectionName = $model->getConnectionName();
            $tableName = $model->getTable();

            $schema = \Illuminate\Support\Facades\Schema::connection($connectionName);
            $tableColumns = $schema->getColumnListing($tableName);

            return in_array($field, $tableColumns);
        } catch (\Throwable $e) {
            // Log error and return false as fallback
            \Illuminate\Support\Facades\Log::warning('Error checking database column in filter', [
                'model' => get_class($model),
                'field' => $field,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @return Builder<\Illuminate\Database\Eloquent\Model>
     */
    abstract public function apply(Builder $query, mixed $value): Builder;

    abstract public function render(): string;
}
