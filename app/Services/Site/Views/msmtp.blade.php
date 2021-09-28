# {{ $stack_managed }}
defaults
auth           {{ $smtp['username'] && $smtp['password'] ? 'on' : 'off' }}
tls            {{ $smtp['tls'] ? 'on' : 'off' }}
tls_trust_file /etc/ssl/certs/ca-certificates.crt
logfile        ~/.msmtp.log

account        mailrelay
host           {{ $smtp['host'] }}
port           {{ $smtp['port'] }}
from           {{ $smtp['from'] }}

@if($smtp['username'] && $smtp['password'])
user           {{ $smtp['username'] }}
password       {{ $smtp['password'] }}

@endif
account default : mailrelay