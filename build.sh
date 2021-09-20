#!/bin/bash

docker build -t ghcr.io/sitepilot/lshttpd:latest docker/lshttpd/

docker build -t ghcr.io/sitepilot/runtime:7.4 docker/runtime/7.4

docker build -t ghcr.io/sitepilot/runtime:8.0 docker/runtime/8.0
