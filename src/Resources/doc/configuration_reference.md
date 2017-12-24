Configuration reference
=======================

```yml
csa_guzzle:
    profiler:
        enabled:              false

        # The maximum size of the body which should be stored in the profiler (in bytes)
        max_body_size:        65536 # Example: 65536
    logger:
        enabled:              false
        service:              ~
        format:               '{hostname} {req_header_User-Agent} - [{date_common_log}] "{method} {target} HTTP/{version}" {code} {res_header_Content-Length}'
        level:                debug
    default_client:       ~
    cache:
        enabled:              false
        adapter:              ~
    clients:

        # Prototype
        name:
            class:                GuzzleHttp\Client
            lazy:                 false
            config:               ~
            middleware:           []
            alias:                null
    mock:
        enabled:              false
        storage_path:         ~ # Required
        mode:                 replay
        request_headers_blacklist: []
        response_headers_blacklist: []
```

To log request/response body you can use `{req_body}` and `{res_body}` respectively in `format` setting.

Full list of logs variables with description:

| Variable | 	Substitution |
| --- | --- |
| {request}	| Full HTTP request message | 
| {response}	| Full HTTP response message | 
| {ts}	 | Timestamp | 
| {host} |	Host of the request | 
| {method} |	Method of the request | 
| {url}	 | URL of the request | 
| {host} |	Host of the request | 
| {protocol} | 	Request protocol | 
| {version} | Protocol version | 
| {resource}|	Resource of the request (path + query + fragment) | 
| {port}	| Port of the request | 
| {hostname} | 	Hostname of the machine that sent the request | 
| {code} | Status code of the response (if available) | 
| {phrase} | Reason phrase of the response (if available) | 
| {curl_error} | Curl error message (if available) | 
| {curl_code} | Curl error code (if available) | 
| {curl_stderr} | Curl standard error (if available) | 
| {connect_time} | Time in seconds it took to establish the connection (if available) | 
| {total_time}	 | Total transaction time in seconds for last transfer (if available) | 
| {req_header_*} | Replace * with the lowercased name of a request header to add to the message | 
| {res_header_*} | Replace * with the lowercased name of a response header to add to the message | 
| {req_body} | Request body  | 
| {res_body} | Response body|


Reference [Guzzle Log Plugin Docs](http://guzzle3.readthedocs.io/plugins/log-plugin.html#log-plugin)

[Back to index](../../../README.md)
