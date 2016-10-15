#! /usr/bin/env bash

exec docker run -v "$(pwd)":"$(pwd)" --workdir="$(pwd)" --rm hhvm/hhvm:3.15-lts-latest hhvm -c test.ini test.php
