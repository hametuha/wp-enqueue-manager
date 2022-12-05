#!/usr/bin/env bash

set -e

echo 'Making deploy packages...'
rm -rf .gitignore
rm -rf .git
rm -rf .github
rm -rf tests
rm -rf bin
rm -rf composer.lock
rm -rf vendor
