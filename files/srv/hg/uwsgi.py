# -*- coding: utf-8 -*- 

import os, subprocess
from mercurial.ui import ui
from mercurial.hgweb.hgwebdir_mod import hgwebdir

BASE_DIR = os.path.dirname(__file__)
hgweb_config = "/srv/hg/hgweb.config"


"""
  Deny error
"""  
def out_deny():
    res = ""
    res += "<div style='color: red; font-weigh: bold; text-align: center;'>"
    res += "Access Denied"
    res += "</div>"
    res += "<div style='text-align: center;'>"
    res += "<a href='/skv'>[back]</a>"
    res += "</div>"
    return res



"""
Mercurial App
"""    
def application_mercurial(env, start_response):
    
    # Save environment
    old_env = os.environ
    
    route_prefix = ""
    if 'HTTP_X_ROUTE_PREFIX' in env:
        route_prefix = env['HTTP_X_ROUTE_PREFIX']
    
    #env['REMOTE_USER'] = env['HTTP_REMOTE_USER']
    env['SCRIPT_NAME'] = route_prefix + env['HTTP_SCRIPT_NAME']
    env['PATH_INFO'] = route_prefix + env['PATH_INFO']
    env['PATH_INFO'] = env['PATH_INFO'][ len(env['SCRIPT_NAME']): ]
    env['HGENCODING'] = 'UTF-8'
    env['HGRCPATH'] = hgweb_config
    env["LANG"] = "en_US.UTF-8"
    env["LANGUAGE"] = "en_US.UTF-8"
    os.environ["LANG"] = "en_US.UTF-8"
    os.environ["LANGUAGE"] = "en_US.UTF-8"
    
    # Set web.prefix
    baseui = ui.load()
    baseui.setconfig(b'web', b'prefix', env['SCRIPT_NAME'].encode())
    
    # Create hgweb app
    application = hgwebdir(hgweb_config, baseui)
    
    # Restore environment
    os.environ = old_env
    
    return application(env, start_response)



"""
  uWSGI App
"""
def application(env, start_response):
    
    return application_mercurial(env, start_response)
    
    os.environ = old_env
    content = out_deny()
    start_response('403', [('Content-Type','text/html')])
    return [content]
