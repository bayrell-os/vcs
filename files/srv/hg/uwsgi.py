# -*- coding: utf-8 -*- 

import os, subprocess, sqlite3
from mercurial.ui import ui
from mercurial.hgweb.hgwebdir_mod import hgwebdir

BASE_DIR = os.path.dirname(__file__)
hgweb_config = "/srv/hg/hgweb.config"

db_con = sqlite3.connect('/data/db/vcs.db')
db_con.row_factory = sqlite3.Row

debug = False

"""
    Return route prefix
"""
def get_route_prefix(env):
   
    route_prefix = ""
    if 'HTTP_X_FORWARDED_PREFIX' in env:
        route_prefix = env['HTTP_X_FORWARDED_PREFIX']
    
    return route_prefix


"""
    Returns app URI
"""
def get_app_uri(env):
    
    route_prefix = get_route_prefix(env)
    script_name = env['HTTP_SCRIPT_NAME']
    path_info = route_prefix + env['PATH_INFO']
    path_info = path_info[ len(script_name): ]
    
    return path_info


"""
    Return user login
"""
def get_user_login(env):
   
    user_login = ""
    if 'HTTP_JWT_AUTH_USER' in env:
        user_login = env['HTTP_JWT_AUTH_USER']
    
    return user_login


"""
  Deny error
"""  
def out_deny(env, start_response):
    route_prefix = get_route_prefix(env)
    res = ""
    res += "<div style='color: red; font-weight: bold; text-align: center; padding: 20px;'>"
    res += "Access Denied"
    res += "</div>"
    res += "<div style='text-align: center;'>"
    res += "<a href='" + route_prefix + "/'>[back]</a>"
    res += "</div>"
    start_response('403', [('Content-Type','text/html')])
    return [ res.encode('utf-8') ]


"""
    Returns current project name
"""
def find_project(project_name):
    
    if len(project_name) == 0:
        return ""
    
    project_name_arr = project_name.split("/")
    
    if project_name_arr[0] == "":
        project_name_arr = project_name_arr[1:]
    
    for i in range(min(3, len(project_name_arr)), 0, -1):
        project_name = "/".join( project_name_arr[0: i] )
        project = get_project(project_name)
        if project is not None:
            return project_name
        
    return ""


"""
    Get project by name
"""
def get_project(project_name):
    
    sql = """
        select * from projects
        where projects.type="hg" and projects.name=:project_name and projects.is_deleted=0
    """
    
    cur = db_con.cursor()
    res = cur.execute(sql, {"project_name": project_name})
    project = res.fetchone();
    cur.close()
    
    return project;
    
    
"""
    Find user by name
"""
def find_user(user_login):
    
    sql = """
        select * from users
        where users.login=:user_login and users.banned=0 and users.is_deleted=0
    """
    
    cur = db_con.cursor()
    res = cur.execute(sql, {"user_login": user_login})
    user = res.fetchone();
    cur.close()
    
    return user;    
    

"""
    Check repository access level
    0 - access deny
    1 - read only
    2 - write
"""
def check_access_level(user_login, project_name):
    
    user = find_user(user_login)
    if user is None:
        return 0
    
    sql = """
        select
            projects.id as project_id,
            projects.type as project_type,
            projects.name as project_name,
            projects_users.name as user_name,
            projects_users.value as access_value
        
        from projects

        inner join projects_users on (projects_users.project_id=projects.id)

        where
            projects.type="hg" and
            projects.name=:project_name and
            projects.is_deleted=0 and
            (projects_users.name in (

                select
                    "@" || users_roles.name as group_name
                
                from users

                inner join users_in_roles on (users_in_roles.user_id=users.id)
                inner join users_roles on (users_in_roles.role_id=users_roles.id)

                where
                    users.login=:user_login
                
            ) or projects_users.name = :user_login or projects_users.name="@all")

        order by projects_users.value asc;
    """
    
    cur = db_con.cursor()
    res = cur.execute(sql, {"user_login": user_login, "project_name": project_name})
    
    access_value = 0
    for row in res:
        
        if row["user_name"] == user_login:
            access_value = row["access_value"]
            break
        
        if row["access_value"] > access_value:
            access_value = row["access_value"]
    
    cur.close()
    
    return access_value
    
    
"""
Mercurial App
"""    
def application_mercurial(env, start_response, access_level):
    
    # Save environment
    old_env = os.environ
    
    route_prefix = get_route_prefix(env)
    
    env['SCRIPT_NAME'] = env['HTTP_SCRIPT_NAME']
    env['PATH_INFO'] = route_prefix + env['PATH_INFO']
    env['PATH_INFO'] = env['PATH_INFO'][ len(env['SCRIPT_NAME']): ]
    env['HGENCODING'] = 'UTF-8'
    env['HGRCPATH'] = hgweb_config
    env["LANG"] = "en_US.UTF-8"
    env["LANGUAGE"] = "en_US.UTF-8"
    os.environ["LANG"] = "en_US.UTF-8"
    os.environ["LANGUAGE"] = "en_US.UTF-8"
    #print (env)
    
    # Set web.prefix
    baseui = ui.load()
    baseui.setconfig(b'web', b'prefix', env['SCRIPT_NAME'].encode())
    
    # Set readonly
    if access_level == 1:
        baseui.setconfig(b'web', b'allow_push', "".encode())
        baseui.setconfig(b'web', b'deny_push', "*".encode())
    
    # Create hgweb app
    application = hgwebdir(hgweb_config, baseui)
    
    # Restore environment
    os.environ = old_env
    
    return application(env, start_response)


"""
  uWSGI App
"""
def application(env, start_response):
    
    project_name = get_app_uri(env)
    project_name = find_project(project_name)
    if project_name == "":
        return out_deny(env, start_response)
        
    user_login = get_user_login(env)
    access_level = check_access_level(user_login, project_name)
    
    if debug:
        print ("user_login", user_login)
        print ("project_name", project_name)
        print ("access_level", access_level)
        print (env)
    
    if access_level == 0:
        return out_deny(env, start_response)
    
    return application_mercurial(env, start_response, access_level)
