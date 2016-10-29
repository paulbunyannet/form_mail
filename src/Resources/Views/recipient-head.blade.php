@if( isset($data['greeting']) && strlen($data['greeting']) > 0)
    {!! $data['greeting'] !!}
@endif
@if( isset($data['body']))
    {!! $data['body'] !!}
@endif