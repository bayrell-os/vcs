
select
	projects.id as project_id,
	projects.type as project_type,
	projects.name as project_name
  
from projects

where
	projects.type="hg" and projects.name="docker/test"
	