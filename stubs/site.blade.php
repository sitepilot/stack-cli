# {{ $stack_managed }}
---
services:
  {{ $service->name() }}:
    image: {{ $image }}:{{ $tag }}
    restart: always
    hostname: {{ $service->name() }}
    working_dir: /opt/stack/sites/{{ $service->name() }}/public
    environment:
      RUNTIME_USER_ID: {{ $uid }}
      RUNTIME_USER_HOME: /opt/stack/sites/{{ $service->name() }}
    volumes:
      - "{{ stack_project_path("sites/" . $service->name()) }}:/opt/stack/sites/{{ $service->name() }}"
      - "{{ stack_config_path("config/sites/" . $service->name() . '/msmtp.conf') }}:/etc/msmtprc:ro"
      - "{{ stack_config_path("config/sites/" . $service->name() . '/php.ini') }}:/usr/local/lsws/lsphp80/etc/php/8.0/mods-available/10-stack.ini:ro"
