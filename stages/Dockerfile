ARG ARCH=
FROM bayrell/alpine_php_fpm:8.0${ARCH}

RUN cd ~; \
    apk update; \
	apk upgrade; \
    apk add py3-pip python3-dev gcc musl-dev uwsgi uwsgi-python3 uwsgi-http \
        git git-gitweb git-daemon patch fcgiwrap perl-cgi; \
    pip3 install mercurial==6.1.2; \
    rm -rf /var/cache/apk/*; \
	chmod +x /root/run.sh; \
	echo 'Ok'
    
COPY files /

RUN cd ~; \
    chmod +x /usr/bin/fcgiwrap.sh; \
    patch /usr/lib/python3.9/site-packages/mercurial/config.py < /srv/patch/mercurial.patch; \
    patch /usr/share/gitweb/gitweb.cgi < /srv/patch/gitweb.patch; \
    echo 'Ok'
    