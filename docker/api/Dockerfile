FROM nginx:alpine

COPY nginx.conf /etc/nginx/nginx.conf
COPY default.conf /etc/nginx/conf.d/default.conf

RUN echo "upstream php-upstream { server php:9000; }" > /etc/nginx/conf.d/upstream.conf

RUN adduser -D -u 1000 www-data

CMD ["nginx", "-g", "daemon off;"]

EXPOSE 80