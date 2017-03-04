#! /usr/bin/env sh

exec docker run -v "$(pwd)":"$(pwd)" --workdir="$(pwd)" --rm hhvm/hhvm:3.15-lts-latest hhvm -c test/self-test.ini test/self-test.php
