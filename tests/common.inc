#!/bin/bash

declare -r CURL='curl --insecure --fail --user beng:'$(<'password.txt')
declare -r BASEURL='https://ws2.grid.sara.nl/catchplus/'
declare -r TESTHANDLE='10574/PIETERB_TESTHANDLE'
