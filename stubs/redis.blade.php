# {{ $stack_managed }}
---
services:
  {{ $service->name() }}:
    image: {{ $image }}:{{ $tag }}
    restart: always
    volumes:
      - {{ $service->name() }}:/data

volumes:
  {{ $service->name() }}:
