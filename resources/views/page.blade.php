@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading" style="padding-bottom: 30px">
                    <div class="pull-left">
                      Toto sample web app
                    </div>
                    <div class="pull-right">
                        <a href="/">Home</a>
                    </div>
                </div>

                <div class="panel-body">
                    {!! $data !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
