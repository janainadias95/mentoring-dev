@extends('layouts.dashboardAdmin') 
@section('page_heading','')
@section('section')
@section ('table_panel_title','Editar Profissão')
@section ('table_panel_body')

<?php $profession = Session::get('profissao') ?>
      <form role="form" method="POST" action="{{route('profession.update', $profession->profession_id)}}">
          @method('PATCH')         
          @csrf
            <div class="form-group{{ $errors->has('profession_name') ? ' has-error' : '' }}">
                <label>Nome</label>
                <input class="form-control" name="profession_name" value="{{ $profession->profession_name}}">
                <!-- <p class="help-block">Example block-level help text here.</p> -->
                @if ($errors->has('profession_name'))
                        <small class="text-danger" role="alert">
                            <strong>{{ $errors->first('profession_name') }}</strong>
                        </small>
                @endif
            </div>
            <input type="hidden" value="{{$profession->profession_active}}" name="profession_active">
            <button type="submit" class="btn btn-success btn-circle" data-toggle="tooltip" title="Salvar"><i class="fa fa-check"></i></button> 
            <button type="reset" class="btn btn-default btn-circle" data-toggle="tooltip" title="Limpar"><i class="fa fa-times"></i></button> 
        </form>

@endsection
@include('widgets.panel', array('header'=>true, 'as'=>'table'))
@stop