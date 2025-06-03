<div class="flex flex-wrap gap-4 items-end">
    @foreach($this->getFilters() as $filter)
        <div class="flex-1 min-w-[200px]">
            {!! $filter->render() !!}
        </div>
    @endforeach
</div>
