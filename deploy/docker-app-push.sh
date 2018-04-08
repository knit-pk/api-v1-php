#!/usr/bin/env sh

DOCKER_TAG=$(echo ${TRAVIS_TAG} | sed -E 's~^v(.*)~\1~')

# Docker Hub
echo "${DOCKER_PASSWORD}" | docker login -u "${DOCKER_USERNAME}" --password-stdin

docker build . \
    --cache-from knitpk/api:latest \
    --tag knitpk/api:${DOCKER_TAG} \
    --tag knitpk/api:latest

docker push knitpk/api:${DOCKER_TAG}
docker push knitpk/api:latest

docker build -f Dockerfile.standalone . \
    --cache-from knitpk/api:standalone \
    --tag knitpk/api:${DOCKER_TAG}-standalone \
    --tag knitpk/api:standalone \
    --build-arg KNIT_API_TAG=${DOCKER_TAG} \
    --build-arg KNIT_API_ADMIN_TAG=latest \
    --build-arg KNIT_API_URL=https://d15e2ckuuchn46.cloudfront.net

docker push knitpk/api:${DOCKER_TAG}-standalone
docker push knitpk/api:standalone
