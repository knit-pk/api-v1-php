vcl 4.0;

import std;

backend default {
    .host = "api";
    .port = "80";
    // Health check
    .probe = {
        .url = "/";
        .timeout = 5s;
        .interval = 10s;
        .window = 5;
        .threshold = 3;
    }
}

// Hosts allowed to send BAN requests
acl ban {
    "172.18.0.1"; // debug/cli
    "localhost";
    "backend";
}

sub vcl_backend_response {
    if (bereq.url ~ "^[^?]*\.(?:jpe?g|png)(?:\?.*)?$") {
        unset beresp.http.set-cookie;
        set beresp.do_stream = true;
     }

     // Ban lurker friendly header
     set beresp.http.url = bereq.url;

     // Add a grace in case the backend is down
     set beresp.grace = 1h;

     // Check for ESI acknowledgement and remove Surrogate-Control header
     if (beresp.http.Surrogate-Control ~ "ESI/1.0") {
         unset beresp.http.Surrogate-Control;
         set beresp.do_esi = true;
     }
}

sub vcl_deliver {
    // Don't send cache tags related headers to the client
    unset resp.http.url;

    // Uncomment the following line to NOT send the "Cache-Tags" header to the client (prevent using CloudFlare cache tags)
    //unset resp.http.Cache-Tags;

    // CORS
    set req.http.Access-Control-Allow-Origin = "*";
    
    // Insert Diagnostic header to show Hit or Miss
    if (obj.hits > 0) {
        set resp.http.X-Cache = "Hit";
        set resp.http.X-Cache-Hits = obj.hits;
    } else {
        set resp.http.X-Cache = "Miss";
    }
}

sub vcl_recv {
    // Do not cache media files
    if (req.url ~ "^[^?]*\.(?:jpe?g|png)(?:\?.*)?$") {
        unset req.http.Cookie;
        return (hash);
    }

    // Only cache GET or HEAD requests. This makes sure the POST/PUT/DELETE requests are always passed.
    if (req.method != "GET" && req.method != "HEAD") {
        return (pass);
    }

    if (req.http.X-Forwarded-Proto == "https" ) {
        set req.http.X-Forwarded-Port = "443";
    } else {
        set req.http.X-Forwarded-Port = "80";
    }

    // Remove all cookies except the session ID.
    if (req.http.Cookie) {
        set req.http.Cookie = ";" + req.http.Cookie;
        set req.http.Cookie = regsuball(req.http.Cookie, "; +", ";");
        set req.http.Cookie = regsuball(req.http.Cookie, ";(PHPSESSID)=", "; \1=");
        set req.http.Cookie = regsuball(req.http.Cookie, ";[^ ][^;]*", "");
        set req.http.Cookie = regsuball(req.http.Cookie, "^[; ]+|[; ]+$", "");

        if (req.http.Cookie == "") {
            // If there are no more cookies, remove the header to get page cached.
            unset req.http.Cookie;
        }
    }

    // Add a Surrogate-Capability header to announce ESI support.
    set req.http.Surrogate-Capability = "abc=ESI/1.0";

    // Remove the "Forwarded" HTTP header if exists (security)
    unset req.http.forwarded;

    // To allow API Platform to ban by cache tags
    if (req.method == "BAN") {
        if (client.ip !~ ban) {
            return(synth(405, "Not allowed: " + client.ip));
        }

        if (req.http.ApiPlatform-Ban-Regex) {
            ban("obj.http.Cache-Tags ~ " + req.http.ApiPlatform-Ban-Regex);

            return(synth(200, "Ban added"));
        }

        return(synth(400, "ApiPlatform-Ban-Regex HTTP header must be set."));
    }
}

# From https://github.com/varnish/Varnish-Book/blob/master/vcl/grace.vcl
sub vcl_hit {
    if (obj.ttl >= 0s) {
        // Normal hit
        return (deliver);
    }

    if (std.healthy(req.backend_hint)) {
        // The backend is healthy
        // Fetch the object from the backend
        return (fetch);
    }

    if (obj.ttl + obj.grace > 0s) {
        // No fresh object and the backend is not healthy
        // Deliver graced object
        // Automatically triggers a background fetch
        return (deliver);
    }

    // No valid object to deliver
    // No healthy backend to handle request
    // Return error
    return (synth(503, "Cannot get response from API. It is due to connection problem, or it is down."));
}