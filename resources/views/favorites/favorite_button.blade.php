@if (Auth::user()->is_favorite($micropost->id))
    {{-- お気に入り解除ボタンのフォーム --}}
    {!! Form::open(['route' => ['favorites.unfavorite', $micropost->id], 'method' => 'delete']) !!}
        {!! Form::submit('Unfavorite', ['class' => "btn btn-danger"]) !!}
    {!! Form::close() !!}
@else
    {{-- お気に入りボタンのフォーム --}}
    {!! Form::open(['route' => ['favorites.favorite', $micropost->id]]) !!}
        {!! Form::submit('Favorite', ['class' => "btn btn-primary"]) !!}
    {!! Form::close() !!}
@endif