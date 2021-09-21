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
    restic \
    ca-certificates

# ----- User ----- #

RUN usermod -l runtime -u ${RUNTIME_USER_ID} -d /opt/runtime www-data \
    && groupmod -n runtime -g ${RUNTIME_USER_ID} www-data \
    && chsh -s /bin/bash runtime

# ----- Filesystem ----- #

COPY "bin/entrypoint" \
    "/opt/runtime/bin/entrypoint"

# ----- Config ----- #

WORKDIR /opt/runtime

STOPSIGNAL SIGTERM

ENTRYPOINT ["/opt/runtime/bin/entrypoint"]
