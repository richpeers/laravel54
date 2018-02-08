@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-3">
            <div class="panel panel-primary">
                <div class="panel-heading"><i class="fas fa-code fa-2x"></i>Embed</div>
                <div class="panel-body">
                    <!-- <a href="{{ route('cssE') }}"><i class="fab fa-css3"></i>CSS Examples</a><br> -->
                </div>
            </div>
        </div>
        <div class="col-md-9">
        <h1>embed</h1>
        <img src="{{ $info->image }}">
            <div style="width: 100%; height: 500px;">
            {{$info->title}}
            <hr>
            {{$info->description}}
            <hr>
            {{$info->url}}
            <hr>
            </div>
        </div>
    </div>

     
</div>
@endsection
