@extends('layouts.public')

@section('title', $page->title)

@php
    $pageMetaDescription = is_array($page->builder_data ?? null) && is_string(($page->builder_data['meta_description'] ?? null))
        ? trim((string) $page->builder_data['meta_description'])
        : '';
    $pageMetaImage = is_array($page->builder_data ?? null) && is_string(($page->builder_data['meta_image'] ?? null))
        ? trim((string) $page->builder_data['meta_image'])
        : '';
@endphp

@if($pageMetaDescription !== '')
@section('metaDescription', $pageMetaDescription)
@endif

@if($pageMetaImage !== '')
@section('metaImage', $pageMetaImage)
@endif

@section('content')
    {!! $page->html_content ?? '' !!}
@endsection
