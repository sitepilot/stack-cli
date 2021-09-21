# {{ $stack_managed }}
---
services:
  {{ $service->name() }}:
    image: {{ $image }}:{{ $tag }}
    restart: always
    hostname: {{ $service->name() }}
    working_dir: /etc/caddy
    ports:
      - {{ $ports['http'] }}:80
      - {{ $ports['https'] }}:443
    volumes:
      - {{ $service->name() }}:/data
      - {{ stack_config_path('config/caddy/vhosts') }}:/etc/caddy/vhosts:ro
      - {{ stack_config_path('config/caddy/caddy.conf') }}:/etc/caddy/Caddyfile:ro

volumes:
  {{ $service->name() }}:
