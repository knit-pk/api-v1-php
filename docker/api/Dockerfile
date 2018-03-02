FROM nginx:alpine

COPY nginx.conf /etc/nginx/nginx.conf
COPY default.conf /etc/nginx/conf.d/default.conf

RUN adduser -D -u 1000 www-data

CMD ["nginx", "-g", "daemon off;"]

EXPOSE 80