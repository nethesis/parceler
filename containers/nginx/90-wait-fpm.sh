#!/usr/bin/env sh

set -e

wait-for "${FPM_HOST:?Missing FPM_HOST}:${FPM_PORT:?Missing FPM_PORT}" -t 60
