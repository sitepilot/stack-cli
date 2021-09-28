; {{ $stack_managed }}
date.timezone="{{ $php['timezone'] }}"
post_max_size="{{ $php['uploadSize'] }}"
upload_max_filesize="{{ $php['uploadSize'] }}"
memory_limit="{{ $php['memoryLimit'] }}"

expose_php=Off
short_open_tag=On
max_input_vars={{ $php['maxInputVars'] }}

opcache.enable=1
opcache.max_accelerated_files=10000
opcache.memory_consumption={{ $php['opcacheMemory'] }}
opcache.revalidate_freq=2
opcache.save_comments=0

sendmail_path=/usr/bin/msmtp -t
