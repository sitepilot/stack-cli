# {{ $stack_managed }}
(config) {
  header Server "Stack"
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
