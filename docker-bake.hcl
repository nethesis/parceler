target "production" {
    dockerfile = "containers/Dockerfile"
    context    = "."
    target     = "production"
    output     = [
        "type=docker"
    ]
    tags       = [
        "ghcr.io/nethesis/alpha-centauri:latest"
    ]

}

group "default" {
    targets = ["production"]
}
