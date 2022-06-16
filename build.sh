#!/bin/bash

SCRIPT=$(readlink -f $0)
SCRIPT_PATH=`dirname $SCRIPT`
BASE_PATH=`dirname $SCRIPT_PATH`

RETVAL=0
VERSION=1.0
SUBVERSION=0
IMAGE="vcs"

TAG=`date '+%Y%m%d_%H%M%S'`

case "$1" in
	
	test)
		echo "Build bayrell/$IMAGE:$VERSION.$SUBVERSION-$TAG"
		docker build ./ -t bayrell/$IMAGE:$VERSION.$SUBVERSION-$TAG \
            --file stages/Dockerfile --build-arg ARCH=-amd64
	;;
	
	amd64)
		echo "Build bayrell/$IMAGE:$VERSION.$SUBVERSION-amd64"
		docker build ./ -t bayrell/$IMAGE:$VERSION.$SUBVERSION-amd64 \
			--file stages/Dockerfile --build-arg ARCH=-amd64
	;;
	
	arm64v8)
		echo "Build bayrell/$IMAGE:$VERSION.$SUBVERSION-arm64v8"
		docker build ./ -t bayrell/$IMAGE:$VERSION.$SUBVERSION-arm64v8 \
			--file stages/Dockerfile --build-arg ARCH=-arm64v8
	;;
	
	arm32v7)
		echo "Build bayrell/$IMAGE:$VERSION.$SUBVERSION-arm32v7"
		docker build ./ -t bayrell/$IMAGE:$VERSION.$SUBVERSION-arm32v7 \
			--file stages/Dockerfile --build-arg ARCH=-arm32v7
	;;
	
	manifest)
		rm -rf ~/.docker/manifests/docker.io_bayrell_$IMAGE-*
		
		docker tag bayrell/$IMAGE:$VERSION.$SUBVERSION-amd64 bayrell/$IMAGE:$VERSION-amd64
		docker tag bayrell/$IMAGE:$VERSION.$SUBVERSION-arm64v8 bayrell/$IMAGE:$VERSION-arm64v8
		docker tag bayrell/$IMAGE:$VERSION.$SUBVERSION-arm32v7 bayrell/$IMAGE:$VERSION-arm32v7
		
		docker push bayrell/$IMAGE:$VERSION.$SUBVERSION-amd64
		docker push bayrell/$IMAGE:$VERSION.$SUBVERSION-arm64v8
		docker push bayrell/$IMAGE:$VERSION.$SUBVERSION-arm32v7
		
		docker push bayrell/$IMAGE:$VERSION-amd64
		docker push bayrell/$IMAGE:$VERSION-arm64v8
		docker push bayrell/$IMAGE:$VERSION-arm32v7
		
		docker manifest create bayrell/$IMAGE:$VERSION.$SUBVERSION \
			--amend bayrell/$IMAGE:$VERSION.$SUBVERSION-amd64 \
			--amend bayrell/$IMAGE:$VERSION.$SUBVERSION-arm64v8 \
			--amend bayrell/$IMAGE:$VERSION.$SUBVERSION-arm32v7
		docker manifest push bayrell/$IMAGE:$VERSION.$SUBVERSION
		
		docker manifest create bayrell/$IMAGE:$VERSION \
			--amend bayrell/$IMAGE:$VERSION-amd64 \
			--amend bayrell/$IMAGE:$VERSION-arm64v8 \
			--amend bayrell/$IMAGE:$VERSION-arm32v7
		docker manifest push bayrell/$IMAGE:$VERSION
	;;
	
	all)
		$0 amd64
		$0 arm64v8
		$0 arm32v7
		$0 manifest
	;;
	
	*)
		echo "Build bayrell/$IMAGE:$VERSION.$SUBVERSION"
		echo "Usage: $0 {amd64|arm64v8|arm32v7|manifest|all|test}"
		RETVAL=1

esac

exit $RETVAL
