# {{ $stack_managed }}
---
services:
  {{ $service->name() }}:
    image: {{ $image }}:{{ $tag }}
    restart: always
    environment:
      RUNTIME_USER_ID: {{ $uid }}
      ADMIN_USERNAME: "{{ $username }}"
      ADMIN_PASSWORD: "${STACK_WEB_ADMIN_PASSWORD:?}"
@if($ports['http'] || $ports['https'] || $ports['admin'])
    ports:
@if($ports['http'])
      - {{ $ports['http'] }}:80
@endif
@if($ports['https'])
      - {{ $ports['https'] }}:443
@endif
@if($ports['admin'])
      - {{ $ports['admin'] }}:7080
@endif
@endif
    volumes:
@if(count(\App\Stack::vhosts(true)))
      - "{{ stack_project_path('vhosts') }}:/opt/stack/vhosts"
@endif
      - "{{ stack_config_path('config/lshttpd/vhosts') }}:/usr/local/lsws/conf/vhosts:ro"
      - "{{ stack_config_path('config/lshttpd/lshttpd.conf') }}:/usr/local/lsws/conf/httpd_config.conf:ro"
      - "{{ stack_config_path('config/lshttpd/admin.conf') }}:/usr/local/lsws/admin/conf/admin_config.conf:ro"
