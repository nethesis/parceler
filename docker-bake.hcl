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
}

group "production" {
    targets = ["php", "nginx"]
}
