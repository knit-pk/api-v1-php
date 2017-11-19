#!/usr/bin/env sh

varnishd -a :80 -f /etc/varnish/default.vcl -s malloc,256m
varnishlog