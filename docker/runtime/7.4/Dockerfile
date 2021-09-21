FROM ubuntu:20.04

ENV RUNTIME_USER_ID=1000

ENV PATH="/opt/runtime/bin:${PATH}"

LABEL org.opencontainers.image.source="https://github.com/sitepilot/stack"

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
    ca-certificates \
    nano \
    less

# ----- LSPHP ----- #

RUN wget -O - http://rpms.litespeedtech.com/debian/enable_lst_debian_repo.sh | bash \
    && install-packages \
    lsphp74 \
    lsphp74-apcu \
    lsphp74-common \
    lsphp74-curl \
    lsphp74-igbinary \
    lsphp74-imagick \
    lsphp74-imap \
    lsphp74-intl \
    lsphp74-json \
    lsphp74-ldap \
    lsphp74-memcached \
    lsphp74-msgpack \
    lsphp74-mysql \
    lsphp74-opcache \
    lsphp74-pear \
    lsphp74-pgsql \
    lsphp74-pspell \
    lsphp74-redis \
    lsphp74-sqlite3 \
    lsphp74-tidy

RUN ln -s /usr/local/lsws/lsphp74/bin/php /opt/runtime/bin

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
