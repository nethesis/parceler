target "php" {
    dockerfile = "containers/php/Dockerfile"
    context    = "."
    target     = "production"
    output     = [
        "type=docker"
    ]
    tags       = [
        "ghcr.io/nethesis/parceler-php:latest"
    ]
    cache-from = [
        "type=gha,scope=php"
    ]
    cache-to   = [
        "type=gha,mode=max,scope=php"
    ]
}

target "nginx" {
    dockerfile = "containers/nginx/Dockerfile"
    context    = "."
    output     = [
        "type=docker"
    ]
    tags       = [
        "ghcr.io/nethesis/parceler-nginx:latest"
    ]
    cache-from = [
        "type=gha,scope=nginx"
    ]
    cache-to   = [
        "type=gha,mode=max,scope=nginx"
    ]
}

group "production" {
    targets = ["php", "nginx"]
}

target "testing" {
    dockerfile = "containers/php/Dockerfile"
    context    = "."
    target     = "testing"
    output     = [
        "type=cacheonly"
    ]
    cache-from = [
        "type=gha,scope=php"
    ]
}
