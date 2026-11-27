#!/usr/bin/env bash
#
# Runaris Ghost — duplo clique no Finder (macOS).
#
# O Finder abre .command no Terminal; este arquivo só repassa tudo para o
# start.sh, que é a implementação única compartilhada com o Linux.
#
exec "$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/start.sh" "$@"
