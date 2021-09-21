---
# {{ $stack_managed }}
version: '3'

services:
  svc-proxy:
    image: {{ $proxy['image']}}:{{ $proxy['tag'] }}
    ports:
      - {{ $ports['http'] }}:80
      - {{ $ports['https'] }}:443
    volumes:
      - proxy:/data
      - ./config/caddy/sites:/etc/caddy/sites:ro
      - ./config/caddy/caddy.conf:/etc/caddy/Caddyfile:ro

  svc-web:
    image: ghcr.io/sitepilot/lshttpd:latest
    environment:
      RUNTIME_USER_ID: {{ $uid }}
      ADMIN_USERNAME: "{{ $lshttpd['username'] }}"
      ADMIN_PASSWORD: "{{ $lshttpd['password'] }}"
@if($ports['lshttpd'])
    ports:
      - {{ $ports['lshttpd'] }}:7080
@endif
    volumes:
      - "{{ stack_project_path('sites') }}:/opt/stack/sites"
      - "./config/lshttpd/sites:/usr/local/lsws/conf/sites:ro"
      - "./config/lshttpd/lshttpd.conf:/usr/local/lsws/conf/httpd_config.conf:ro"
      - "./config/lshttpd/admin.conf:/usr/local/lsws/admin/conf/admin_config.conf:ro"

@if($mailhog['enabled'])
  svc-mailhog:
    image: {{ $mailhog['image']}}:{{ $mailhog['tag']}}
    restart: always

@endif
@if($phpmyadmin['enabled'])
  svc-phpmyadmin:
    image: {{ $phpmyadmin['image'] }}:{{ $phpmyadmin['tag'] }}
    environment:
      PMA_HOST: "svc-mysql"
      PMA_ABSOLUTE_URI: "/svc/phpmyadmin"
      UPLOAD_LIMIT: "{{ $phpmyadmin['uploadLimit'] }}"

@endif
@foreach($sites as $name => $site)
  {{ $name }}:
    image: ghcr.io/sitepilot/runtime:{{ $site['runtime'] }}
    working_dir: /opt/stack/sites/{{ $name }}/public
    environment:
      RUNTIME_USER_ID: {{ $uid }}
    volumes:
      - "./config/msmtp.conf:/etc/msmtprc:ro"
      - "{{ stack_project_path("sites/$name") }}:/opt/stack/sites/{{ $name }}"
      - "./config/php.ini:/usr/local/lsws/lsphp80/etc/php/8.0/mods-available/10-stack.ini:ro"

@endforeach

volumes:
  proxy:

