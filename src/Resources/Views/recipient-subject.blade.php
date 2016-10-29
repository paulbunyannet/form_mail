@if (is_string($data['subject']))
    {!! $data['subject'] !!}
@elseif(is_array($data['subject']) && array_key_exists('recipient', $data['subject']))
    {!! $data['subject']['recipient'] !!}
@endif