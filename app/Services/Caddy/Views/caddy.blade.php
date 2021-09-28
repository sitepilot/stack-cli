# {{ $stack_managed }}
(config) {
  header Server "{{ $organization }}"
}

(routes) {
@foreach($routes as $route)
  route {{ $route['path'] }}* {
    uri strip_prefix {{ $route['path'] }}
    reverse_proxy {{ $route['url'] }}
  }
@endforeach
}

import "/etc/caddy/vhosts/*.conf"
