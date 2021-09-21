# {{ $stack_managed }}
@php
$vhostDomains = [];
$port = $ssl['email'] ? ':443' : ':80';
foreach($domains as $domain) {
  $vhostDomain = str_replace('*', '', $domain . $port);
  $vhostDomains[] = $vhostDomain;
  if($domain != '*') {
    $vhostDomains[] = 'www.' . $vhostDomain;
  }
}
@endphp
{{ implode(' ', $vhostDomains) }} {
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
