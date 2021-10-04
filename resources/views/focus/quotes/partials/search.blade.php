<ul class="list-group">
    @foreach($lead as $payer)
        <li class="list-group-item"
            onClick="selectPayer({{json_encode(array('id'=>$payer->id,'name'=>$payer->name,'name'=>$payer->name,'name'=>$payer->name))}})"><p>
                <strong>{{$payer->name}}</strong> {{$payer->email}}</p></li>
    @endforeach
</ul>