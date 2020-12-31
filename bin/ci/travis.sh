#!/bin/bash
set -ex

echo "TRAVIS_JOB_ID: ${TRAVIS_JOB_ID}"
echo "TRAVIS_JOB_NAME: ${TRAVIS_JOB_NAME}"
echo "TRAVIS_JOB_NUMBER: ${TRAVIS_JOB_NUMBER}"
echo "TRAVIS_BUILD_DIR: ${TRAVIS_BUILD_DIR}"
echo "TRAVIS_BUILD_ID: ${TRAVIS_BUILD_ID}"

# Run in background
nohup php -S localhost:8855 -t ${TRAVIS_BUILD_DIR}/tests/test_app ${TRAVIS_BUILD_DIR}/tests/test_app/index.php > phpd.log 2>&1 &
# Get last background process PID
PHP_SERVER_PID=$!

# Run integration tests
composer run tests-integration
SUCCESS=$?

kill -3 $PHP_SERVER_PID

exit $SUCCESS
