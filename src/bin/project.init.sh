#!/bin/bash

PROJECT_TYPE=$1
PROJECT_ID=$2

if [ -z "$PROJECT_TYPE" ]; then
	exit 1
fi

if [ "$PROJECT_TYPE" != "hg" ] && [ "$PROJECT_TYPE" != "git" ]; then
	exit 1
fi

if [ -z "$PROJECT_ID" ]; then
	exit 1
fi

if [ "$PROJECT_TYPE" == "hg" ]; then
	
	if [ ! -d "/data/repo/id/$PROJECT_ID" ]; then
		mkdir "/data/repo/id/$PROJECT_ID"
	fi
	
	if [ ! -z "$(ls -A /data/repo/id/$PROJECT_ID)" ]; then
		exit 1
	fi
	
	cd /data/repo/id
	cd $PROJECT_ID
	hg init
	exit 0
	
fi

if [ "$PROJECT_TYPE" == "git" ]; then
	
	if [ ! -d "/data/repo/id/$PROJECT_ID" ]; then
		mkdir "/data/repo/id/$PROJECT_ID"
	fi
	
	if [ ! -z "$(ls -A /data/repo/id/$PROJECT_ID)" ]; then
		exit 1
	fi
	
	cd /data/repo/id
	cd $PROJECT_ID
	git init --bare
	git config --file config http.receivepack true
	exit 0
	
fi
