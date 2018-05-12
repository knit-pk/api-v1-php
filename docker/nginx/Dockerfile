FROM knitpk/api-admin:latest as api-admin

ARG KNIT_API_URL=http://localhost
RUN REACT_APP_API_URL=$KNIT_API_URL yarn build

FROM alpine:3.7

COPY docker-app-start.sh /usr/local/bin/docker-app-start
RUN apk add --no-cache nginx && \
    chmod +x /usr/local/bin/docker-app-start

COPY nginx.conf /etc/nginx/nginx.conf
COPY conf/* /etc/nginx/conf.d/
COPY --from=api-admin /usr/src/app/build /usr/src/api/public/admin

ENV PORT 80
ENV API_URL http://api
CMD ["docker-app-start"]
