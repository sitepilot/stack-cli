# {{ $stack_managed }}
---
services:
  {{ $service->name() }}:
    image: {{ $image }}:{{ $tag }}
    restart: always
    working_dir: /opt/stack/vhosts/{{ $service->name() }}/public
    environment:
      RUNTIME_USER_ID: {{ $uid }}
    volumes:
      - "{{ stack_project_path("vhosts/" . $service->name()) }}:/opt/stack/vhosts/{{ $service->name() }}"
      - "{{ stack_project_path("vhosts/" . $service->name() . '/config/msmtp.conf') }}:/etc/msmtprc:ro"
      - "{{ stack_project_path("vhosts/" . $service->name() . '/config/php.ini') }}:/usr/local/lsws/lsphp80/etc/php/8.0/mods-available/10-stack.ini:ro"
