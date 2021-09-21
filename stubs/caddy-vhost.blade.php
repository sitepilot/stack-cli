# {{ $stack_managed }}
@php
$siteDomains = [];
$port = $ssl['email'] ? ':443' : ':80';
foreach($domains as $domain) {
  $siteDomain = str_replace('*', '', $domain . $port);
  $siteDomains[] = $siteDomain;
  if($domain != '*') {
    $siteDomains[] = 'www.' . $siteDomain;
  }
}
@endphp
{{ implode(' ', $siteDomains) }} {
@if($ssl['email'])
  tls {{ $ssl['email'] }} {
    on_demand
  }

@endif
@if(count($basicAuth))
@foreach($basicAuth as $authConfig)
@if(count($authConfig['users']))
  basicauth {{ $authConfig['path'] }} {
@foreach($authConfig['users'] as $authUser)
    {{ $authUser['username'] }} {{ $authUser['password'] }}
@endforeach
  }

@endif
@endforeach
@endif
  import config

  import routes

  reverse_proxy http://web
}
