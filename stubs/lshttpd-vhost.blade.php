# {{ $stack_managed }}
virtualHost {{ $service->name() }} {
  vhRoot                      /opt/stack/vhosts/$VH_NAME
  docRoot                     $VH_ROOTpublic
  listeners                   http

  vhDomain                    $VH_NAME
  vhAliases                   {!! implode(', ', $domains) !!}

  scripthandler  {
    add                     lsapi:$VH_NAME php
  }

  extprocessor $VH_NAME {
    type                    lsapi
    address                 $VH_NAME:9000
    maxConns                2000
    initTimeout             60
    retryTimeout            0
    respBuffer              0
    autoStart               0
  }

  errorlog $VH_ROOTlogs/error.log {
    useServer               0
    logLevel                NOTICE
    rollingSize             0
  }

  accesslog $VH_ROOTlogs/access.log {
    useServer               0
    logFormat               %a %l %u %t "%r" %>s %O "%{Referer}i" "%{User-Agent}i"
    logHeaders              5
    rollingSize             0
  }

  phpIniOverride  {
    php_admin_value mail.log "$VH_ROOTlogs/php-mail.log"
    php_admin_value error_log "$VH_ROOTlogs/php-error.log"
  }
}
