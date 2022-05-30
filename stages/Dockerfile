ARG ARCH=
FROM bayrell/alpine_php_fpm:8.0${ARCH}

RUN cd ~; \
    apk update; \
	apk upgrade; \
    apk add py3-pip python3-dev gcc musl-dev uwsgi uwsgi-python3 uwsgi-http git patch; \
    pip3 install mercurial==6.1.2; \
    rm -rf /var/cache/apk/*; \
	chmod +x /root/run.sh; \
	echo 'Ok'
    
COPY files/etc /etc
COPY files/root /root
COPY files/srv /srv

RUN cd ~; \
    patch /usr/lib/python3.9/site-packages/mercurial/config.py < /srv/patch/mercurial.patch; \
    echo 'Ok'
    