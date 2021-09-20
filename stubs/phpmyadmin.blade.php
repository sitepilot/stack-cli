# {{ $stack_managed }}
---
services:
  {{ $service->name() }}:
    image: {{ $image }}:{{ $tag }}
    restart: always
    environment:
      PMA_HOST: "mysql"
      PMA_ABSOLUTE_URI: "/svc/phpmyadmin"
      UPLOAD_LIMIT: "{{ $uploadLimit }}"
