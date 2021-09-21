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
    gnupg

# ----- Openlitespeed ----- #

RUN wget -O - http://rpms.litespeedtech.com/debian/enable_lst_debian_repo.sh | bash \
    && install-packages \
    openlitespeed

# ----- User ----- #

RUN usermod -l runtime -u ${RUNTIME_USER_ID} -d /opt/runtime/home www-data \
    && groupmod -n runtime -g ${RUNTIME_USER_ID} www-data \
    && chsh -s /bin/bash runtime

# ----- Filesystem ----- #

RUN mkdir --parents \
    "/opt/runtime/logs" \
    "/opt/runtime/tmp/gzcache" \
    "/opt/runtime/tmp/stats" \
    "/opt/runtime/tmp/swap" \
    "/opt/runtime/tmp/upload"

RUN touch \
    "/opt/runtime/logs/server.log" \
    "/opt/runtime/logs/access.log"

COPY "bin/entrypoint" \
    "/opt/runtime/bin/entrypoint"

COPY "bin/reload" \
    "/opt/runtime/bin/reload"

RUN chown --recursive "${RUNTIME_USER_ID}:${RUNTIME_USER_ID}" \
    "/opt/runtime"

# ----- Config ----- #

EXPOSE 80

WORKDIR /opt/runtime

STOPSIGNAL SIGTERM

ENTRYPOINT ["/opt/runtime/bin/entrypoint"]
