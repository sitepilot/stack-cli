FROM ubuntu:20.04

ARG RUNTIME_USER_ID=1000

ENV RUNTIME_USER_ID=${RUNTIME_USER_ID}

ENV PATH="/opt/runtime/bin:${PATH}"

# ----- Build Files ----- #

RUN mkdir --parents \
    "/opt/runtime/bin"

COPY "bin/install-packages" \
    "/opt/runtime/bin/install-packages"

# ----- Packages ----- #

RUN install-packages \
    wget \
    curl \
    gnupg \
    msmtp \
    ca-certificates

# ----- LSPHP ----- #

RUN wget -O - http://rpms.litespeedtech.com/debian/enable_lst_debian_repo.sh | bash \
    && install-packages \
    lsphp80 \
    lsphp80-apcu \
    lsphp80-common \
    lsphp80-curl \
    lsphp80-igbinary \
    lsphp80-imagick \
    lsphp80-imap \
    lsphp80-intl \
    lsphp80-ldap \
    lsphp80-memcached \
    lsphp80-msgpack \
    lsphp80-mysql \
    lsphp80-opcache \
    lsphp80-pear \
    lsphp80-pgsql \
    lsphp80-pspell \
    lsphp80-redis \
    lsphp80-sqlite3 \
    lsphp80-tidy

RUN ln -s /usr/local/lsws/lsphp80/bin/php /opt/runtime/bin

# ----- Composer ----- #

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php \
    && mv composer.phar /usr/local/bin/composer \
    && php -r "unlink('composer-setup.php');" \
    && composer --version

# ----- WPCLI ----- #

RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    && chmod +x wp-cli.phar \
    && mv wp-cli.phar /usr/local/bin/wp \
    && wp --allow-root --version

# ----- NodeJS ---- #

RUN curl -fsSL https://deb.nodesource.com/setup_14.x | bash - \
    && install-packages nodejs

# ----- User ----- #

RUN usermod -l runtime -u ${RUNTIME_USER_ID} -d /opt/runtime www-data \
    && groupmod -n runtime -g ${RUNTIME_USER_ID} www-data \
    && chsh -s /bin/bash runtime

# ----- Filesystem ----- #

COPY "bin/entrypoint" \
    "/opt/runtime/bin/entrypoint"

RUN chown --recursive "${RUNTIME_USER_ID}:${RUNTIME_USER_ID}" \
    "/opt/runtime"

# ----- Config ----- #

EXPOSE 9000

WORKDIR /opt/runtime

STOPSIGNAL SIGTERM

ENTRYPOINT ["/opt/runtime/bin/entrypoint"]
