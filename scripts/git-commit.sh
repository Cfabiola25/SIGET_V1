#!/usr/bin/env bash
set -e

if [ -z "$1" ]; then
  echo "Uso: ./scripts/git-commit.sh 'feat(v1): mensaje'"
  exit 1
fi

git add .
git commit -m "$1"

echo "Commit creado: $1"