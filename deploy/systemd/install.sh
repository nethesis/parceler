#!/usr/bin/env sh

set -e

# check if first argument is a valid directory
if [ ! -d "$1" ]; then
  echo "Error: $1 is not a valid version either 'v4' or 'v5'"
  exit 1
fi

if [ "$1" = 'v4' ]; then
    INSTALL_PATH="$HOME/.config/systemd/user"
    install -Dm644 v4/container-nginx.service "$INSTALL_PATH/container-nginx.service"
    install -Dm644 v4/container-php.service "$INSTALL_PATH/container-php.service"
    install -Dm644 v4/container-scheduler.service "$INSTALL_PATH/container-scheduler.service"
    install -Dm644 v4/container-worker.service "$INSTALL_PATH/container-worker.service"
    install -Dm644 v4/pod-parceler.service "$INSTALL_PATH/pod-parceler.service"
    systemctl --user daemon-reload
    systemctl --user reload-or-restart pod-parceler
else
    INSTALL_PATH="$HOME/.config/containers/systemd"
    install -Dm644 v5/nginx.container "$INSTALL_PATH/nginx.container"
    install -Dm644 v5/parceler.pod "$INSTALL_PATH/parceler.pod"
    install -Dm644 v5/php.container "$INSTALL_PATH/php.container"
    install -Dm644 v5/scheduler.container "$INSTALL_PATH/scheduler.container"
    install -Dm644 v5/storage.volume "$INSTALL_PATH/storage.volume"
    install -Dm644 v5/worker.container "$INSTALL_PATH/worker.container"
    systemctl --user daemon-reload
    systemctl --user reload-or-restart parceler-pod
fi
