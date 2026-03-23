@extends('layouts.public')

@section('title', $page->title)

@section('content')
    {!! $page->html_content ?? '' !!}
@endsection
