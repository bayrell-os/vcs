select
	projects.id as project_id,
	projects.type as project_type,
	projects.name as project_name,
	projects_users.name as user_name,
	projects_users.value as access_value
  
from projects

inner join projects_users on (projects_users.project_id=projects.id)

where project_type="hg" and project_name="docker/test" and
(projects_users.name in (

	select
		"@" || users_groups.name as group_name
	
	from users

	inner join users_in_groups on (users_in_groups.user_id=users.id)
	inner join users_groups on (users_in_groups.group_id=users_groups.id)

	where
		users.login="test"
	
) or projects_users.name = "test" or projects_users.name="@all")

order by projects_users.value asc;