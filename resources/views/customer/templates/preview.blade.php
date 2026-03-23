<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Preview: {{ $template->name }}</title>
    @php
        $fontFamily = \App\Models\Setting::get('admin_font_family', 'Inter');
        $fontWeights = \App\Models\Setting::get('admin_font_weights', '400,500,600,700');
        $fontWeightsUrl = preg_replace('/\s*,\s*/', ';', $fontWeights);
        $fontFamilyUrl = str_replace(' ', '+', $fontFamily);
        $googleFontsUrl = "https://fonts.googleapis.com/css2?family={$fontFamilyUrl}:wght@{$fontWeightsUrl}&display=swap";
    @endphp
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="{{ $googleFontsUrl }}" rel="stylesheet" />
    <style>
        body {
            margin: 0;
            padding: 20px;
            background: #f3f4f6;
            font-family: '{{ $fontFamily }}', sans-serif;
        }
        .preview-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        .preview-header {
            background: #1f2937;
            color: white;
            padding: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .preview-content {
            padding: 0;
        }
    </style>
</head>
<body>
    <div class="preview-container">
        <div class="preview-header">
            <h1 style="margin: 0; font-size: 18px;">{{ $template->name }}</h1>
            <button onclick="window.close()" style="background: #ef4444; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;">Close</button>
        </div>
        <div class="preview-content">
            {!! $template->html_content ?? '<p>No content available</p>' !!}
        </div>
    </div>
</body>
</html>

