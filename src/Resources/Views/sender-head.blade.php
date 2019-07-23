<p style="font-size: 150%; font-weight: 700;">Thanks for filling out the {!! title_case(str_replace(['-','_'], [' ', ' '], $data['formName'])) !!} form!</p>
<p>A Message was sent to {{ $data['recipient'] }} {{ isset($data['name']) && strlen($data['name']) > 0 ? '( '.$data['name'] . ' )' : ''  }} at {{ $data['time'] }}</p>
<p>Message that was sent below:</p>
@if( isset($data['body']))
    <div style="border: 2px solid #cccccc; background: #cccccc; padding: 10px; margin: 10px">
        {!! $data['body'] !!}
    </div>
@endif
