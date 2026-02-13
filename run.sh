#!/bin/bash

SERVICE=${2:-}

function build {
    eval "docker-compose build --progress=plain --no-cache ${SERVICE}"
}

function up {
  docker-compose up -d
}

function redeploy {
  eval "docker-compose up -d --force-recreate ${SERVICE}"
}

function down {
  docker-compose down -v
}

function ps {
  docker-compose ps -a
}

function config {
  docker-compose config
}

function enter {
  docker-compose  exec ${SERVICE} /bin/bash
}

function logs {
  eval "docker-compose logs ${*}"
}

"${@}"
