#!/bin/bash

PROJECT_TYPE=$1
PROJECT_NAME=$2

if [ -z "$PROJECT_TYPE" ]; then
	exit 1
fi

if [ "$PROJECT_TYPE" != "hg" ] && [ "$PROJECT_TYPE" != "git" ]; then
	exit 1
fi

if [ -z "$PROJECT_NAME" ]; then
	exit 1
fi

if [ "$PROJECT_TYPE" == "hg" ]; then
	
	if [ ! -d "/data/repo/hg/$PROJECT_NAME" ]; then
		exit 1
	fi
	
	if [ ! -z "$(ls -A /data/repo/hg/$PROJECT_NAME)" ]; then
		exit 1
	fi
	
	cd /data/repo/hg
	cd $PROJECT_NAME
	hg init
	exit 0
	
fi

if [ "$PROJECT_TYPE" == "git" ]; then
	
	if [ ! -d "/data/repo/git/$PROJECT_NAME" ]; then
		exit 1
	fi
	
	if [ ! -z "$(ls -A /data/repo/git/$PROJECT_NAME)" ]; then
		exit 1
	fi
	
	cd /data/repo/git
	cd $PROJECT_NAME
	git init --bare
	exit 0
	
fi
