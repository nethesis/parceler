#!/usr/bin/env sh

set -e

INSTALL_PATH=${INSTALL_PATH:-"$HOME/.config/containers/systemd"}
# Ensure the directory exists
mkdir -p "${INSTALL_PATH}"
# Install the service files
install -Dm644 parceler.pod "${INSTALL_PATH}/parceler.pod"
install -Dm644 storage.volume "${INSTALL_PATH}/storage.volume"
install -Dm644 php.container "${INSTALL_PATH}/php.container"
install -Dm644 nginx.container "${INSTALL_PATH}/nginx.container"
install -Dm644 scheduler.container "${INSTALL_PATH}/scheduler.container"
install -Dm644 worker.container "${INSTALL_PATH}/worker.container"
# Reload the systemd user service
systemctl --user daemon-reload
systemctl --user reload-or-restart parceler-pod
