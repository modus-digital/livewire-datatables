<?php

declare(strict_types=1);

namespace ModusDigital\LivewireDatatables\Columns;

use Closure;
use Deprecated;
use Illuminate\Support\Str;

class Column
{
    protected string $name;

    protected ?string $field = null;

    protected ?string $relationship = null;

    protected ?string $sortField = null;

    protected ?Closure $sortCallback = null;

    protected bool $sortable = false;

    protected bool $searchable = false;

    protected ?Closure $formatCallback = null;

    protected bool $hidden = false;

    protected ?string $width = null;

    protected ?string $align = null;

    protected ?string $view = null;

    /**
     * Create a new Column instance.
     *
     * @param  string  $name  The field name or display name
     * @param  string|null  $label  Optional display label - if provided, $name becomes the field
     */
    public function __construct(string $name, ?string $label = null)
    {
        if ($label !== null) {
            // When label is provided, $name is the field and $label is the display name
            $this->name = $label;
            $this->field = $name;
        } else {
            // Original behavior: $name is used for both display and field
            $this->name = Str::headline($name);
            $this->field = Str::snake($name);
        }
    }

    /**
     * Create a new Column instance.
     *
     * @param  string  $name  The field name or display name
     * @param  string|null  $label  Optional display label - if provided, $name becomes the field
     */
    public static function make(string $name, ?string $label = null): static
    {
        return new static($name, $label);
    }

    /**
     * Set the display label for the column.
     *
     * @param  string  $label  The display label
     */
    public function label(string $label): self
    {
        $this->name = $label;

        return $this;
    }

    /**
     * Set the field name for the column.
     *
     * @param  string  $field  The field name
     */
    public function field(string $field): self
    {
        $this->field = $field;

        return $this;
    }

    #[Deprecated(message: 'Please use the field method instead', since: '1.2.1')]
    public function relationship(string $relationship): self
    {
        trigger_error('The relationship() method is deprecated. Use field() method instead.', E_USER_DEPRECATED);

        $this->relationship = $relationship;

        return $this;
    }

    /**
     * Set the field to use for sorting.
     *
     * @param  string  $sortField  The field name to sort by
     */
    public function sortField(string $sortField): self
    {
        $this->sortField = $sortField;

        return $this;
    }

    /**
     * Set a custom sorting callback.
     *
     * @param  Closure  $callback  The sorting callback function
     */
    public function sortUsing(Closure $callback): self
    {
        $this->sortCallback = $callback;

        return $this;
    }

    /**
     * Make the column sortable.
     *
     * @param  bool  $sortable  Whether the column should be sortable
     */
    public function sortable(bool $sortable = true): self
    {
        $this->sortable = $sortable;

        return $this;
    }

    /**
     * Make the column searchable.
     *
     * @param  bool  $searchable  Whether the column should be searchable
     */
    public function searchable(bool $searchable = true): self
    {
        $this->searchable = $searchable;

        return $this;
    }

    /**
     * Set a formatting callback for the column value.
     *
     * @param  Closure  $callback  The formatting callback function
     */
    public function format(Closure $callback): self
    {
        $this->formatCallback = $callback;

        return $this;
    }

    /**
     * Hide the column from display.
     *
     * @param  bool  $hidden  Whether the column should be hidden
     */
    public function hidden(bool $hidden = true): self
    {
        $this->hidden = $hidden;

        return $this;
    }

    /**
     * Set the column width.
     *
     * @param  string  $width  The column width (e.g., '100px', '20%')
     */
    public function width(string $width): self
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Set the column text alignment.
     *
     * @param  string  $align  The alignment (left, center, right)
     */
    public function align(string $align): self
    {
        $this->align = $align;

        return $this;
    }

    /**
     * Set a custom view for the column.
     *
     * @param  string  $view  The view name
     */
    public function view(string $view): self
    {
        $this->view = $view;

        return $this;
    }

    /**
     * Get the display name of the column.
     *
     * @return string The column display name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the field name for database queries.
     *
     * @return string The field name
     */
    public function getField(): string
    {
        return $this->field ?? $this->name;
    }

    #[Deprecated(message: 'Please use the field method instead', since: '1.2.1')]
    public function getRelationship(): ?string
    {
        trigger_error('The getRelationship() method is deprecated. Use field() method with dot notation instead.', E_USER_DEPRECATED);

        return $this->relationship;
    }

    /**
     * Check if the column is sortable.
     *
     * @return bool True if the column is sortable
     */
    public function isSortable(): bool
    {
        return $this->sortable;
    }

    /**
     * Check if the column is searchable.
     *
     * @return bool True if the column is searchable
     */
    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    /**
     * Check if the column is hidden.
     *
     * @return bool True if the column is hidden
     */
    public function isHidden(): bool
    {
        return $this->hidden;
    }

    /**
     * Get the column width.
     *
     * @return string|null The column width or null if not set
     */
    public function getWidth(): ?string
    {
        return $this->width;
    }

    /**
     * Get the column text alignment.
     *
     * @return string|null The alignment or null if not set
     */
    public function getAlign(): ?string
    {
        return $this->align;
    }

    /**
     * Get the custom view for the column.
     *
     * @return string|null The view name or null if not set
     */
    public function getView(): ?string
    {
        return $this->view;
    }

    /**
     * Get the formatted value for the column from a record.
     *
     * @param  mixed  $record  The record to extract value from
     * @return mixed The formatted value
     */
    public function getValue(mixed $record): mixed
    {
        $value = $this->extractValue($record);

        if ($value instanceof \BackedEnum) {
            $value = $value->value;
        } elseif ($value instanceof \UnitEnum) {
            $value = $value->name;
        }

        if ($this->formatCallback) {
            return call_user_func($this->formatCallback, $value, $record);
        }

        return $value;
    }

    /**
     * Extract the raw value from a record.
     *
     * @param  mixed  $record  The record to extract value from
     * @return mixed The raw value
     */
    protected function extractValue(mixed $record): mixed
    {
        // Check deprecated relationship property first for backward compatibility
        if ($this->relationship) {
            return data_get($record, $this->relationship);
        }

        return data_get($record, $this->getField());
    }

    /**
     * Get the field name to use for sorting.
     *
     * @return string The sort field name
     */
    public function getSortField(): string
    {
        if ($this->sortField) {
            return $this->sortField;
        }

        // Check deprecated relationship property first for backward compatibility
        if ($this->relationship) {
            return $this->relationship;
        }

        return $this->getField();
    }

    /**
     * Get the custom sorting callback.
     *
     * @return Closure|null The sorting callback or null if not set
     */
    public function getSortCallback(): ?Closure
    {
        return $this->sortCallback;
    }

    /**
     * Check if the column has a custom sorting callback.
     *
     * @return bool True if a custom sorting callback is set
     */
    public function hasSortCallback(): bool
    {
        return $this->sortCallback !== null;
    }
}
