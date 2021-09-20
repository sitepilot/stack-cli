# {{ $stack_managed }}
enableCoreDump            0
sessionTimeout            3600

errorlog /opt/runtime/logs/admin-error.log {
  useServer               1
  logLevel                INFO
  rollingSize             10M
}

accesslog /opt/runtime/logs/admin-access.log {
  useServer               1
  rollingSize             10M
  keepDays                90
}

accessControl  {
  allow                   ALL
}

listener adminListener {
  address                 *:7080
  secure                  0
}
