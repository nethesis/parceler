target "production" {
    dockerfile = "containers/Dockerfile"
    context    = "."
    target     = "production"
    output     = [
        "type=docker"
    ]
    tags       = [
        "ghcr.io/nethesis/parceler:latest"
    ]

}

group "default" {
    targets = ["production"]
}
