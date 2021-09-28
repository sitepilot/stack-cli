# {{ $stack_managed }}
---
services:
  {{ $service->name() }}:
    image: {{ $service->image() }}
    restart: always
    hostname: {{ $service->name() }}
    working_dir: {{ $service->workdir() }}
@if(count($service->environment()))
    environment:
@foreach($service->environment() as $key => $value)
      {{ $key }}: {{ $value }}
@endforeach
@endif
@if(count($service->ports()))
    ports:
@foreach($service->ports() as $key => $value)
      - {{ $key }}:{{ $value }}
@endforeach
@endif
@if(count($service->volumes()))
    volumes:
@foreach($service->volumes() as $key => $value)
      - "{{ $key }}:{{ $value }}"
@endforeach
@endif

@if(count($service->namedVolumes()))
volumes:
@foreach($service->namedVolumes() as $volume)
  {{ $volume }}:
@endforeach
@endif
