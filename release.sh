#!/bin/sh
set -e

[ -n "$1" ]

# finalise docs
composer build-doc
if [ -d "docs/$1" ]; then
    rm -rf "docs/$1"
fi
mv "docs/master" "docs/$1"
rm "docs/latest"
ln -rs "docs/$1" "docs/latest"

# set version
echo "$1" > VERSION

# commit release changes
git add docs VERSION
git commit -m "Release: $1"

# tag release
exec git tag -s "$@"
