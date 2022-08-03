#!/bin/bash

SCRIPT=$(readlink -f $0)
SCRIPT_PATH=`dirname $SCRIPT`
BASE_PATH=`dirname $SCRIPT_PATH`

RETVAL=0
VERSION=1.0
SUBVERSION=0
IMAGE="bayrell/vcs"

TAG=`date '+%Y%m%d_%H%M%S'`

case "$1" in
	
	test)
		echo "Build $IMAGE:$VERSION.$SUBVERSION-$TAG"
		docker build ./ -t $IMAGE:$VERSION.$SUBVERSION-$TAG \
            --file stages/Dockerfile --build-arg ARCH=-amd64
		docker tag $IMAGE:$VERSION.$SUBVERSION-$TAG $IMAGE:$VERSION.$SUBVERSION
		docker tag $IMAGE:$VERSION.$SUBVERSION-$TAG $IMAGE:$VERSION
	;;
	
	amd64)
		echo "Build $IMAGE:$VERSION.$SUBVERSION-amd64"
		docker build ./ -t $IMAGE:$VERSION.$SUBVERSION-amd64 \
			--file stages/Dockerfile --build-arg ARCH=-amd64
	;;
	
	arm64v8)
		echo "Build $IMAGE:$VERSION.$SUBVERSION-arm64v8"
		docker build ./ -t $IMAGE:$VERSION.$SUBVERSION-arm64v8 \
			--file stages/Dockerfile --build-arg ARCH=-arm64v8
	;;
	
	arm32v7)
		echo "Build $IMAGE:$VERSION.$SUBVERSION-arm32v7"
		docker build ./ -t $IMAGE:$VERSION.$SUBVERSION-arm32v7 \
			--file stages/Dockerfile --build-arg ARCH=-arm32v7
	;;
	
	manifest)
		rm -rf ~/.docker/manifests/docker.io_bayrell_vcs-*
		
		docker tag $IMAGE:$VERSION.$SUBVERSION-amd64 $IMAGE:$VERSION-amd64
		docker tag $IMAGE:$VERSION.$SUBVERSION-arm64v8 $IMAGE:$VERSION-arm64v8
		docker tag $IMAGE:$VERSION.$SUBVERSION-arm32v7 $IMAGE:$VERSION-arm32v7
		
		docker push $IMAGE:$VERSION.$SUBVERSION-amd64
		docker push $IMAGE:$VERSION.$SUBVERSION-arm64v8
		docker push $IMAGE:$VERSION.$SUBVERSION-arm32v7
		
		docker push $IMAGE:$VERSION-amd64
		docker push $IMAGE:$VERSION-arm64v8
		docker push $IMAGE:$VERSION-arm32v7
		
		docker manifest create $IMAGE:$VERSION.$SUBVERSION \
			--amend $IMAGE:$VERSION.$SUBVERSION-amd64 \
			--amend $IMAGE:$VERSION.$SUBVERSION-arm64v8 \
			--amend $IMAGE:$VERSION.$SUBVERSION-arm32v7
		docker manifest push $IMAGE:$VERSION.$SUBVERSION
		
		docker manifest create $IMAGE:$VERSION \
			--amend $IMAGE:$VERSION-amd64 \
			--amend $IMAGE:$VERSION-arm64v8 \
			--amend $IMAGE:$VERSION-arm32v7
		docker manifest push $IMAGE:$VERSION
	;;
	
	all)
		$0 amd64
		$0 arm64v8
		$0 arm32v7
		$0 manifest
	;;
	
	*)
		echo "Build $IMAGE:$VERSION.$SUBVERSION"
		echo "Usage: $0 {amd64|arm64v8|arm32v7|manifest|all|test}"
		RETVAL=1

esac

exit $RETVAL
