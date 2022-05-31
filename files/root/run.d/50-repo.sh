if [ ! -d /data/repo ]; then
    mkdir -p /data/repo
    chown -R www:www /data/repo
fi
if [ ! -d /data/repo/git ]; then
    mkdir -p /data/repo/git
    chown -R www:www /data/repo/git
fi
if [ ! -d /data/repo/hg ]; then
    mkdir -p /data/repo/hg
    chown -R www:www /data/repo/hg
fi