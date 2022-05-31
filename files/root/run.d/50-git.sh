if [ ! -d /data/git ]; then
    mkdir -p /data/git
    chown -R www:www /data/git
fi