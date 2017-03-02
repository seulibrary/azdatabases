<ul id="navigation">
    @foreach ($navigation as $key => $value)
        <li>
            <router-link to="/area/{!! $key !!}">{!! title_case(str_replace('_', ' ', $key)) !!}</router-link>
            @if (is_array($value))
                <ul>
                @foreach ($value as $k => $v)
                    <li><router-link to="/area/{!! $key !!}/{!! $v !!}">{!! title_case(str_replace('_', ' ', $v)) !!}</router-link></li>
                @endforeach
                </ul>
            @endif
        </li>
    @endforeach
</ul>