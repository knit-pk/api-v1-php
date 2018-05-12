#!/usr/bin/env sh
echo "NGINX_PORT=$PORT"
echo "NGINX_API_URL=$API_URL"
sed -i "s#NGINX_PORT#$PORT#g" /etc/nginx/conf.d/default.conf
sed -i "s#NGINX_API_URL#$API_URL#g" /etc/nginx/conf.d/default.conf
nginx -g 'daemon off;'
