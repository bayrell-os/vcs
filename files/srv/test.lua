local function bus_call(url, data)
	
    local cjson = require "cjson"
    
	local cloud_os_gateway = os.getenv("CLOUD_OS_GATEWAY")
	local cloud_os_key = os.getenv("CLOUD_OS_KEY")
	local data_keys = {}
    local time = os.time()
    
    -- Add slash to url
    if string.sub(url, 1, 1) == "/" then url = string.sub(url, 2) end
    if string.sub(url, -1, -1) ~= "/" then url = url .. "/" end
    
    -- Build url
    url = "http://" .. cloud_os_gateway .. "/api/bus/" .. url
    
    -- Build data_keys
    for k, v in pairs(data) do
        table.insert(data_keys, k)
    end
    
    -- Sort data_keys
    table.sort(data_keys)
    
    -- Insert time and cloud os key to data_keys
    table.insert(data_keys, time)
    table.insert(data_keys, cloud_os_key)
    
    -- Get text from data_key
    local text = table.concat(data_keys, "|")
    
    -- Get sign
    local md5 = require 'md5'
    local sign = md5.sumhexa(text)
    
    -- Get post data
    local post_data = {}
    post_data.alg = "md5"
    post_data.data = data
    post_data.time = time
    post_data.sign = sign
    post_data = cjson.encode(post_data)
    
    -- Send request
    local cURL = require("cURL")
    local out = ""
    
    c = cURL.easy_init()
    c = cURL.easy{
        url = url,
        post = true,
        httpheader = {
            "Content-Type: application/json";
        },
        postfields = post_data,
    }
    c:perform({ writefunction = function(str)
        out = out .. str
    end})
    
    obj = cjson.decode(out)
    
    if obj.error.code == 1 then
        return obj.result.jwt
    end
    
    return ""
    
end


local function check_htpasswd_os(login, password)
	
	local space_uid = os.getenv("CLOUD_OS_SPACE_UID")
	local data = {}
   
    data.login = login
    data.password = password
    data.space_uid = space_uid
    
	local jwt_str = bus_call("/space/login", data)
	
	if jwt_str == "" then
        return 0
    end
    
    return 1
end



local is_auth = check_htpasswd_os("test", "test")
print (is_auth)
