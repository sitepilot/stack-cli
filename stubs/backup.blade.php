# {{ $stack_managed }}
---
services:
  {{ $service->name() }}:
    image: {{ $image }}:{{ $tag }}
    restart: always
    hostname: {{ $service->name() }}
    working_dir: /opt/stack
    environment:
      RUNTIME_USER_ID: {{ $uid }}
      RESTIC_PASSWORD: ${STACK_BACKUP_PASSWORD:?}
@if('s3' == $strategy)
      AWS_ACCESS_KEY_ID: ${STACK_BACKUP_S3_KEY:?}
      AWS_SECRET_ACCESS_KEY: ${STACK_BACKUP_S3_SECRET:?}
@endif
    volumes:
@if(count(\App\Stack::sites(true)))
      - "{{ stack_project_path('sites') }}:/opt/stack/sites"
@endif
      - "{{ stack_config_path('backups') }}:/opt/stack/backups"
