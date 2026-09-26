@extends('layouts.app')

@section('content')

    

    @include('partials.stats')
    <br>

    <div class="row g-4">

        @include('partials.sales-chart')

        @include('partials.income-chart')

        @include('partials.location')

    </div>

@endsection