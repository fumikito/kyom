#!/usr/bin/env bash

set -e

# Set variables.
PREFIX="refs/tags/"
VERSION=${1#"$PREFIX"}

# Install packages.
composer install --no-dev --prefer-dist --no-suggest

# Install NPM.
npm install
npm run package

# Generate readme.txt
curl -L https://raw.githubusercontent.com/fumikito/wp-readme/master/wp-readme.php | php


# Change version string.
sed -i.bak "s/Version: .*/Version: ${VERSION}/g" ./style.css
sed -i.bak "s/^Stable Tag: .*/Stable Tag: ${VERSION}/g" ./readme.txt

# Remove unwanted files in distignore.
files=(`cat ".distignore"`)

for item in "${files[@]}"; do
  if [ -e $item ]; then
    rm -frv $item
  fi
done

