<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NavigationMenu;
use App\Models\Page;
use Illuminate\Http\Request;

class NavigationMenuController extends Controller
{
    public function edit()
    {
        $menu = NavigationMenu::firstOrCreate(['key' => 'public_main'], ['items' => []]);

        $pages = Page::query()
            ->where('type', 'page')
            ->where('status', 'publish')
            ->orderBy('title')
            ->get(['id', 'title', 'slug']);

        return view('admin.menus.edit', [
            'menu' => $menu,
            'pages' => $pages,
        ]);
    }

    public function update(Request $request)
    {
        $menu = NavigationMenu::firstOrCreate(['key' => 'public_main'], ['items' => []]);

        $data = $request->validate([
            'items' => ['nullable', 'string'],
        ]);

        $itemsRaw = $data['items'] ?? null;
        $items = [];
        if (is_string($itemsRaw) && trim($itemsRaw) !== '') {
            $decoded = json_decode($itemsRaw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $items = $decoded;
            }
        }

        $menu->update([
            'items' => $this->sanitizeItems($items),
        ]);

        return redirect()->route('admin.menus.edit')->with('success', __('Menu updated.'));
    }

    private function sanitizeItems(array $items): array
    {
        $clean = [];

        $allowedRoutes = [
            'home',
            'features',
            'pricing',
            'blog.index',
            'roadmap',
            'docs',
            'api.docs.public',
        ];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $type = $item['type'] ?? null;
            if ($type === 'route') {
                $route = $item['route'] ?? null;
                $label = $item['label'] ?? null;

                if (!is_string($route) || !in_array($route, $allowedRoutes, true)) {
                    continue;
                }

                if (!is_string($label) || trim($label) === '') {
                    $label = $route;
                }

                $clean[] = [
                    'type' => 'route',
                    'route' => $route,
                    'label' => trim((string) $label),
                ];
                continue;
            }

            if ($type === 'page') {
                $pageId = $item['page_id'] ?? null;
                $label = $item['label'] ?? null;

                if (!is_numeric($pageId)) {
                    continue;
                }

                $page = Page::query()
                    ->where('id', (int) $pageId)
                    ->where('type', 'page')
                    ->where('status', 'publish')
                    ->first(['id', 'title']);

                if (!$page) {
                    continue;
                }

                if (!is_string($label) || trim($label) === '') {
                    $label = $page->title;
                }

                $clean[] = [
                    'type' => 'page',
                    'page_id' => (int) $page->id,
                    'label' => trim((string) $label),
                ];
            }
        }

        return $clean;
    }
}
