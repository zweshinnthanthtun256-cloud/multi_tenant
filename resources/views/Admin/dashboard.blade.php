@extends('layouts.app')

@section('content')

    @include('partials.topbar')

    @include('partials.stats')

    <div class="row g-4">

        @include('partials.sales-chart')

        @include('partials.income-chart')

        @include('partials.location')

    </div>

@endsection