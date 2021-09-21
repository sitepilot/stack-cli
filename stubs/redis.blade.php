# {{ $stack_managed }}
---
services:
  {{ $service->name() }}:
    image: {{ $image }}:{{ $tag }}
    restart: always
    hostname: {{ $service->name() }}
    volumes:
      - {{ $service->name() }}:/data

volumes:
  {{ $service->name() }}:
