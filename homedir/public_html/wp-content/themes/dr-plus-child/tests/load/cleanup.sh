#!/usr/bin/env bash
# Remove all test bookings/logs for the isolated 2099-05 test dates.
set -eu
node helpers/cleanup.js
