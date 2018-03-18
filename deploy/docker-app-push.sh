#!/usr/bin/env sh

DOCKER_TAG=$(echo ${TRAVIS_TAG} | sed -E 's~^v(.*)~\1~')

# Docker Hub
docker login -u "${DOCKER_USERNAME}" -p "${DOCKER_PASSWORD}"
docker build . -t knitpk/api:${DOCKER_TAG}
docker push knitpk/api:${DOCKER_TAG}

docker build -f Dockerfile.standalone . -t knitpk/api:${DOCKER_TAG}-standalone --build-arg KNIT_API_TAG=${DOCKER_TAG}
docker push knitpk/api:${DOCKER_TAG}-standalone