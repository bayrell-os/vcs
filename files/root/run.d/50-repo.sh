if [ ! -d /data/home ]; then
    mkdir -p /data/home
    chown -R www:www /data/home
fi

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

if [ ! -f /data/home/.gitconfig ]; then
    cp /srv/git/.gitconfig /data/home
    
if [ `cmp -s /srv/git/.gitconfig /data/home/.gitconfig` ]; then
    rm /data/home/.gitconfig
    cp /srv/git/.gitconfig /data/home
    echo "Copy .gitconfig to /data/home"
