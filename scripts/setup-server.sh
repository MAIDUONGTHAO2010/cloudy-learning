#!/usr/bin/env bash
# ---------------------------------------------------------------------------
# setup-server.sh
# Bootstrap an Ubuntu 24.04 server (e.g. AWS EC2 t3.small) with the three
# dependencies required to run Cloudy Learning:
#   1. Git
#   2. Docker Engine
#   3. Docker Compose Plugin (v2  →  `docker compose`)
# ---------------------------------------------------------------------------
set -euo pipefail

DOCKER_GPG_KEY=/etc/apt/keyrings/docker.asc
DOCKER_SOURCES=/etc/apt/sources.list.d/docker.list

# ---------------------------------------------------------------------------
# 1. System update
# ---------------------------------------------------------------------------
echo "==> Updating package index …"
sudo apt-get update -y
sudo apt-get upgrade -y

# ---------------------------------------------------------------------------
# 2. Git
# ---------------------------------------------------------------------------
echo "==> Installing Git …"
sudo apt-get install -y git
git --version

# ---------------------------------------------------------------------------
# 3. Docker Engine prerequisites
# ---------------------------------------------------------------------------
echo "==> Installing Docker prerequisites …"
sudo apt-get install -y \
    ca-certificates \
    curl \
    gnupg \
    lsb-release

# ---------------------------------------------------------------------------
# 4. Docker's official GPG key
# ---------------------------------------------------------------------------
echo "==> Adding Docker GPG key …"
sudo install -m 0755 -d /etc/apt/keyrings
sudo curl -fsSL https://download.docker.com/linux/ubuntu/gpg \
    -o "$DOCKER_GPG_KEY"
sudo chmod a+r "$DOCKER_GPG_KEY"

# Verify the GPG key fingerprint matches Docker's documented value:
# 9DC8 5822 9FC7 DD38 854A  E2D8 8D81 803C 0EBF CD88
EXPECTED_FP="9DC858229FC7DD38854AE2D88D81803C0EBFCD88"
ACTUAL_FP=$(gpg --no-default-keyring --keyring "gnupg-ring:${DOCKER_GPG_KEY}" \
    --fingerprint 2>/dev/null \
    | grep -A1 "^pub" \
    | tail -1 \
    | tr -d ' ')
if [ "$ACTUAL_FP" != "$EXPECTED_FP" ]; then
    echo "ERROR: Docker GPG key fingerprint mismatch!" >&2
    echo "  Expected : $EXPECTED_FP" >&2
    echo "  Got      : $ACTUAL_FP" >&2
    exit 1
fi
echo "==> GPG key fingerprint verified."

# ---------------------------------------------------------------------------
# 5. Docker stable repository
# ---------------------------------------------------------------------------
echo "==> Adding Docker repository …"
echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=${DOCKER_GPG_KEY}] \
https://download.docker.com/linux/ubuntu \
$(. /etc/os-release && echo "${UBUNTU_CODENAME:-$VERSION_CODENAME}") stable" \
  | sudo tee "$DOCKER_SOURCES" > /dev/null

sudo apt-get update -y

# ---------------------------------------------------------------------------
# 6. Docker Engine + CLI + containerd + Compose plugin
# ---------------------------------------------------------------------------
echo "==> Installing Docker Engine and Docker Compose plugin …"
sudo apt-get install -y \
    docker-ce \
    docker-ce-cli \
    containerd.io \
    docker-buildx-plugin \
    docker-compose-plugin

# ---------------------------------------------------------------------------
# 7. Add current user to the docker group (avoids sudo for every command)
# ---------------------------------------------------------------------------
echo "==> Adding ${USER} to the docker group …"
sudo usermod -aG docker "$USER"

# ---------------------------------------------------------------------------
# 8. Enable and start Docker
# ---------------------------------------------------------------------------
echo "==> Enabling Docker service …"
sudo systemctl enable --now docker

# ---------------------------------------------------------------------------
# 9. Smoke-test
# ---------------------------------------------------------------------------
echo ""
echo "==> Versions installed:"
git --version
docker --version
docker compose version

echo ""
echo "==> Done! Log out and back in (or run 'newgrp docker') so group membership takes effect."
