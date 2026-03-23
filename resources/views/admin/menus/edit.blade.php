@extends('layouts.admin')

@section('title', __('Menus'))
@section('page-title', __('Menus'))

@section('content')
<div class="space-y-6" x-data>
    <x-card>
        <form method="POST" action="{{ route('admin.menus.update') }}" id="menu-form" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                <div class="lg:col-span-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-admin-text-primary">{{ __('Add Page') }}</label>
                        <div class="mt-1 flex gap-2">
                            <select id="add-page-id" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                <option value="">{{ __('Select a page') }}</option>
                                @foreach($pages as $p)
                                    <option value="{{ $p->id }}">{{ $p->title }}</option>
                                @endforeach
                            </select>
                            <button type="button" id="btn-add-page" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-md shadow-sm hover:bg-primary-700">{{ __('Add') }}</button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-admin-text-primary">{{ __('Add Built-in Link') }}</label>
                        <div class="mt-1 flex gap-2">
                            <select id="add-route" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                <option value="">{{ __('Select a route') }}</option>
                                <option value="home">home</option>
                                <option value="features">features</option>
                                <option value="pricing">pricing</option>
                                <option value="blog.index">blog.index</option>
                                <option value="roadmap">roadmap</option>
                                <option value="docs">docs</option>
                                <option value="api.docs.public">api.docs.public</option>
                            </select>
                            <button type="button" id="btn-add-route" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-md shadow-sm hover:bg-primary-700">{{ __('Add') }}</button>
                        </div>
                    </div>

                    <div class="pt-2">
                        <x-button type="submit" variant="primary">{{ __('Save Menu') }}</x-button>
                    </div>

                    <input type="hidden" name="items" id="items" value="">
                </div>

                <div class="lg:col-span-7">
                    <div class="flex items-center justify-between">
                        <div class="text-sm font-medium text-gray-900 dark:text-admin-text-primary">{{ __('Menu Items') }}</div>
                        <div class="text-xs text-gray-500 dark:text-admin-text-secondary">{{ __('Drag to reorder') }}</div>
                    </div>

                    <div class="mt-3 rounded-lg border border-admin-border bg-white/5">
                        <ul id="menu-items" class="divide-y divide-admin-border"></ul>
                    </div>
                </div>
            </div>
        </form>
    </x-card>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const initialItems = @json($menu->items ?? []);
    const pagesById = @json($pages->keyBy('id'));

    let items = Array.isArray(initialItems) ? initialItems : [];

    const listEl = document.getElementById('menu-items');
    const itemsInput = document.getElementById('items');

    function sync() {
        itemsInput.value = JSON.stringify(items);
    }

    function labelFor(item) {
        if (item.type === 'page') {
            const p = pagesById[String(item.page_id)] || null;
            return item.label || (p ? p.title : 'Page');
        }
        return item.label || (item.route || 'Route');
    }

    function render() {
        listEl.innerHTML = '';

        items.forEach(function (item, index) {
            const li = document.createElement('li');
            li.className = 'flex items-center justify-between px-4 py-3';
            li.draggable = true;
            li.dataset.index = String(index);

            const left = document.createElement('div');
            left.className = 'flex items-center gap-3 min-w-0';

            const handle = document.createElement('div');
            handle.className = 'text-admin-text-secondary cursor-move select-none';
            handle.textContent = '≡';

            const text = document.createElement('div');
            text.className = 'min-w-0';

            const title = document.createElement('div');
            title.className = 'text-sm font-medium text-gray-900 dark:text-admin-text-primary truncate';
            title.textContent = labelFor(item);

            const meta = document.createElement('div');
            meta.className = 'text-xs text-gray-500 dark:text-admin-text-secondary truncate';
            meta.textContent = item.type === 'page' ? ('page #' + item.page_id) : ('route: ' + item.route);

            text.appendChild(title);
            text.appendChild(meta);

            left.appendChild(handle);
            left.appendChild(text);

            const right = document.createElement('div');
            right.className = 'flex items-center gap-2';

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'text-sm text-red-600 hover:text-red-700';
            removeBtn.textContent = 'Remove';
            removeBtn.addEventListener('click', function () {
                items.splice(index, 1);
                sync();
                render();
            });

            right.appendChild(removeBtn);

            li.appendChild(left);
            li.appendChild(right);

            li.addEventListener('dragstart', function (e) {
                e.dataTransfer.setData('text/plain', li.dataset.index);
            });

            li.addEventListener('dragover', function (e) {
                e.preventDefault();
            });

            li.addEventListener('drop', function (e) {
                e.preventDefault();
                const fromIndex = Number(e.dataTransfer.getData('text/plain'));
                const toIndex = Number(li.dataset.index);

                if (!Number.isFinite(fromIndex) || !Number.isFinite(toIndex) || fromIndex === toIndex) {
                    return;
                }

                const moved = items.splice(fromIndex, 1)[0];
                items.splice(toIndex, 0, moved);
                sync();
                render();
            });

            listEl.appendChild(li);
        });

        sync();
    }

    document.getElementById('btn-add-page').addEventListener('click', function () {
        const pageId = Number(document.getElementById('add-page-id').value);
        if (!Number.isFinite(pageId) || !pageId) {
            return;
        }
        items.push({ type: 'page', page_id: pageId });
        sync();
        render();
    });

    document.getElementById('btn-add-route').addEventListener('click', function () {
        const route = String(document.getElementById('add-route').value || '').trim();
        if (!route) {
            return;
        }
        items.push({ type: 'route', route: route, label: route });
        sync();
        render();
    });

    render();
})();
</script>
@endpush
