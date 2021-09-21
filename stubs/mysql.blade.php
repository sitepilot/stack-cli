# {{ $stack_managed }}
---
services:
  {{ $service->name() }}:
    image: {{ $image }}:{{ $tag }}
    restart: always
    hostname: {{ $service->name() }}
    environment:
      MYSQL_DATABASE: {{ $database }}
      MYSQL_USER: {{ $username }}
      MYSQL_PASSWORD: ${STACK_MYSQL_PASSWORD:?}
      MYSQL_ROOT_PASSWORD: ${STACK_MYSQL_ROOT_PASSWORD:?}
    volumes:
      - {{ $service->name() }}:/var/lib/mysql

volumes:
  {{ $service->name() }}:
