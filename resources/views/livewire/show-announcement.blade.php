@php
    $announcementId = request('announcementId');
    $announcement = getAnnouncementId($announcementId);
@endphp
@extends('layouts.admin')

@section('content')
    <div class="page-body">
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title mx-auto">{{ $announcement['title'] }}</h2>
                </div>
                <div class="card-body p-4">
                    <div>{!! $announcement['description'] !!}</div>
                </div>
            </div>
        </div>
    </div>
@endsection
