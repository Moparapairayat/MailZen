<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BuiltInTemplateSetting;
use App\Models\PublicTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class TemplateImportController extends Controller
{
    public function importGallery(Request $request)
    {
        $tab = (string) $request->query('tab', 'templates');
        if ($tab !== 'templates') {
            $tab = 'templates';
        }

        $builder = (string) $request->query('builder', 'unlayer');
        if (!in_array($builder, ['grapesjs', 'unlayer'], true)) {
            $builder = 'unlayer';
        }

        $fileItems = $this->fileGalleryItems($builder, $tab);

        $publicTemplates = PublicTemplate::query()
            ->where('is_active', true)
            ->where('builder', $builder)
            ->orderBy('name')
            ->get()
            ->map(function (PublicTemplate $template) {
                $settings = is_array($template->settings) ? $template->settings : [];
                $category = is_string($settings['category'] ?? null) && trim((string) $settings['category']) !== ''
                    ? trim((string) $settings['category'])
                    : 'other';

                return [
                    'id' => 'public-' . $template->id,
                    'name' => $template->name,
                    'description' => $template->description,
                    'thumbnail' => $template->thumbnail,
                    'category' => $category,
                    'builder' => $template->builder,
                    'source' => 'public',
                    'is_ai' => false,
                    'content_url' => route('admin.templates.import.public.content', $template),
                ];
            })
            ->values()
            ->all();

        $merged = array_values(array_merge($publicTemplates, $fileItems));

        usort($merged, function ($a, $b) {
            return strcmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? ''));
        });

        $categories = [
            ['id' => 'all', 'name' => 'All Templates', 'icon' => 'grid'],
            ['id' => 'marketing', 'name' => 'Marketing & Promotional', 'icon' => 'megaphone'],
            ['id' => 'transactional', 'name' => 'Transactional', 'icon' => 'receipt'],
            ['id' => 'automation', 'name' => 'Automation & Drip', 'icon' => 'zap'],
            ['id' => 'cold', 'name' => 'Cold & Outreach', 'icon' => 'send'],
            ['id' => 'relationship', 'name' => 'Relationship & Engagement', 'icon' => 'heart'],
            ['id' => 'support', 'name' => 'Support & System', 'icon' => 'headphones'],
            ['id' => 'ecommerce', 'name' => 'E-commerce', 'icon' => 'shopping'],
            ['id' => 'ai', 'name' => 'AI Generated', 'icon' => 'sparkles'],
            ['id' => 'other', 'name' => 'Other', 'icon' => 'folder'],
        ];

        return response()->json([
            'tab' => $tab,
            'templates' => $merged,
            'categories' => $categories,
        ]);
    }

    public function importFileContent(Request $request, string $key)
    {
        $payload = $this->fileGalleryPayloadByKey('unlayer', $key);
        if ($payload === null) {
            abort(404);
        }

        return response()->json($payload);
    }

    public function importPublicContent(PublicTemplate $publicTemplate)
    {
        if (!$publicTemplate->is_active) {
            abort(404);
        }

        return response()->json([
            'id' => $publicTemplate->id,
            'name' => $publicTemplate->name,
            'description' => $publicTemplate->description,
            'html_content' => $publicTemplate->html_content,
            'plain_text_content' => $publicTemplate->plain_text_content,
            'builder' => $publicTemplate->builder,
            'builder_data' => $publicTemplate->builder_data,
        ]);
    }

    private function fileGalleryRoot(string $builder): string
    {
        $builder = trim($builder);
        if ($builder === '') {
            $builder = 'unlayer';
        }

        return resource_path('template-gallery/' . $builder);
    }

    private function fileGalleryItems(string $builder, string $tab): array
    {
        if ($builder !== 'unlayer') {
            return [];
        }

        $root = $this->fileGalleryRoot($builder);
        if (!is_dir($root)) {
            return [];
        }

        $dir = $tab === 'pro' ? ($root . DIRECTORY_SEPARATOR . 'pro') : $root;
        if (!is_dir($dir)) {
            return [];
        }

        $files = File::glob($dir . DIRECTORY_SEPARATOR . '*.json');
        if (!is_array($files)) {
            return [];
        }

        $items = [];
        foreach ($files as $path) {
            if (!is_string($path) || $path === '' || !is_file($path)) {
                continue;
            }

            $relative = ltrim(str_replace($root, '', $path), DIRECTORY_SEPARATOR);
            $key = sha1($builder . ':' . $relative);

            $name = pathinfo($path, PATHINFO_FILENAME);
            $description = null;
            $thumbnail = null;
            $category = 'other';

            try {
                $raw = File::get($path);
                if (is_string($raw) && $raw !== '') {
                    $decoded = json_decode($raw, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        if (is_string($decoded['name'] ?? null) && trim((string) $decoded['name']) !== '') {
                            $name = trim((string) $decoded['name']);
                        }
                        if (is_string($decoded['description'] ?? null) && trim((string) $decoded['description']) !== '') {
                            $description = trim((string) $decoded['description']);
                        }
                        if (is_string($decoded['thumbnail'] ?? null) && trim((string) $decoded['thumbnail']) !== '') {
                            $thumbnail = trim((string) $decoded['thumbnail']);
                        }
                        if (is_string($decoded['category'] ?? null) && trim((string) $decoded['category']) !== '') {
                            $category = trim((string) $decoded['category']);
                        }
                    }
                }
            } catch (\Throwable $e) {
            }

            $setting = BuiltInTemplateSetting::firstOrCreate(
                [
                    'builder' => $builder,
                    'template_key' => $key,
                ],
                [
                    'relative_path' => $relative,
                    'name' => $name,
                    'is_active' => true,
                ]
            );

            if (($setting->name ?? null) !== $name || ($setting->relative_path ?? null) !== $relative) {
                $setting->forceFill([
                    'name' => $name,
                    'relative_path' => $relative,
                ])->save();
            }

            if (!$setting->is_active) {
                continue;
            }

            $items[] = [
                'id' => $key,
                'name' => $name,
                'description' => $description,
                'thumbnail' => $thumbnail,
                'category' => $category,
                'builder' => 'unlayer',
                'source' => 'file',
                'is_ai' => false,
                'content_url' => route('admin.templates.import.file.content', ['key' => $key]),
            ];
        }

        usort($items, function ($a, $b) {
            return strcmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? ''));
        });

        return $items;
    }

    private function fileGalleryPayloadByKey(string $builder, string $key): ?array
    {
        if ($builder !== 'unlayer') {
            return null;
        }

        $root = $this->fileGalleryRoot($builder);
        if (!is_dir($root)) {
            return null;
        }

        $files = File::allFiles($root);
        foreach ($files as $file) {
            $path = $file->getPathname();
            if (!is_string($path) || !str_ends_with(strtolower($path), '.json')) {
                continue;
            }

            $relative = ltrim(str_replace($root, '', $path), DIRECTORY_SEPARATOR);
            $computed = sha1($builder . ':' . $relative);
            if (!hash_equals($computed, $key)) {
                continue;
            }

            $setting = BuiltInTemplateSetting::firstOrCreate(
                [
                    'builder' => $builder,
                    'template_key' => $key,
                ],
                [
                    'relative_path' => $relative,
                    'name' => pathinfo($path, PATHINFO_FILENAME),
                    'is_active' => true,
                ]
            );

            if (!$setting->is_active) {
                return null;
            }

            $raw = File::get($path);
            $decoded = json_decode((string) $raw, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                return null;
            }

            $design = null;
            if (is_array($decoded['design'] ?? null)) {
                $design = $decoded['design'];
            } elseif (!isset($decoded['design'])) {
                $design = $decoded;
            }

            if (!is_array($design)) {
                return null;
            }

            $name = is_string($decoded['name'] ?? null) && trim((string) $decoded['name']) !== ''
                ? trim((string) $decoded['name'])
                : pathinfo($path, PATHINFO_FILENAME);

            if (($setting->name ?? null) !== $name || ($setting->relative_path ?? null) !== $relative) {
                $setting->forceFill([
                    'name' => $name,
                    'relative_path' => $relative,
                ])->save();
            }

            $description = is_string($decoded['description'] ?? null) && trim((string) $decoded['description']) !== ''
                ? trim((string) $decoded['description'])
                : null;

            $html = $this->unlayerPreviewHtmlFromDesign($design);

            $plain = is_string($decoded['plain_text_content'] ?? null) ? (string) $decoded['plain_text_content'] : null;
            if ($plain === null && is_string($decoded['plain'] ?? null)) {
                $plain = (string) $decoded['plain'];
            }

            return [
                'id' => $key,
                'name' => $name,
                'description' => $description,
                'html_content' => $html,
                'plain_text_content' => $plain,
                'builder' => 'unlayer',
                'builder_data' => $design,
            ];
        }

        return null;
    }

    private function unlayerPreviewHtmlFromDesign(array $design): string
    {
        $projectId = config('services.unlayer.project_id');
        $projectId = is_numeric($projectId) ? (int) $projectId : null;

        $designJson = json_encode(
            $design,
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
        );
        if (!is_string($designJson) || trim($designJson) === '') {
            $designJson = 'null';
        }

        $projectIdJson = $projectId !== null ? json_encode($projectId) : 'null';

        return <<<HTML
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    html, body { margin: 0; padding: 0; background: #f9fafb; height: 100%; }
    #status {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      height: 100%;
      min-height: 400px;
      font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial;
      font-size: 14px;
      font-weight: 500;
      color: #6b7280;
    }
    #status .spinner {
      width: 40px;
      height: 40px;
      border: 3px solid #e5e7eb;
      border-top-color: #4f46e5;
      border-radius: 50%;
      animation: spin 0.8s linear infinite;
      margin-bottom: 16px;
    }
    @keyframes spin {
      to { transform: rotate(360deg); }
    }
    #output { width: 100%; background: #ffffff; }
    #editor { width: 1px; height: 1px; overflow: hidden; position: absolute; left: -9999px; top: -9999px; }
  </style>
</head>
<body>
  <div id="status"><div class="spinner"></div>Rendering preview…</div>
  <div id="output"></div>
  <div id="editor"></div>

  <script src="https://editor.unlayer.com/embed.js"></script>
  <script>
    (function () {
      try {
        var design = $designJson;
        var projectId = $projectIdJson;

        if (!design || typeof design !== 'object') {
          document.getElementById('status').textContent = 'Preview not available: invalid Unlayer design JSON.';
          return;
        }

        var initOptions = { id: 'editor', displayMode: 'email' };
        if (projectId) {
          initOptions.projectId = Number(projectId);
        }

        unlayer.init(initOptions);

        unlayer.addEventListener('editor:ready', function () {
          unlayer.loadDesign(design);
          unlayer.exportHtml(function (data) {
            var html = (data && data.html) ? String(data.html) : '';
            if (!html.trim()) {
              document.getElementById('status').textContent = 'Preview not available.';
              return;
            }
            document.getElementById('status').remove();
            document.getElementById('output').innerHTML = html;
          });
        });
      } catch (e) {
        document.getElementById('status').textContent = 'Preview not available.';
      }
    })();
  </script>
</body>
</html>
HTML;
    }
}
