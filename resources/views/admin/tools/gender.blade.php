<div class="btn-group" data-toggle="buttons">
    @foreach($options as $option => $label)
        <label class="btn btn-default btn-sm {{ \Request::get('status', 0) == $option ? 'active' : '' }}">
            <input type="radio" class="member-gender" value="{{ $option }}">{{$label}}
        </label>
    @endforeach
</div>