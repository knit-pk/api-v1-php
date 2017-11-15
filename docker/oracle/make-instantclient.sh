#!/usr/bin/env bash

rm -rf instantclient_12_2 instantclient

unzip instantclient-12c/instantclient-basic-linux.x64-12.2.0.1.0.zip
unzip instantclient-12c/instantclient-sdk-linux.x64-12.2.0.1.0.zip
unzip instantclient-12c/instantclient-sqlplus-linux.x64-12.2.0.1.0.zip

mv instantclient_12_2 instantclient

rm -rf ../php/instantclient && mv instantclient ../php/

