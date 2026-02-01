#!/usr/bin/env sh
set -eu

# Generate a runtime config file for the frontend so we don't need to rebuild
# the image for each environment.
#
# Configure in Coolify:
# - VITE_API_BASE_URL=https://<backend-domain>

cat > /usr/share/nginx/html/env.js <<EOF
// Generated at container start.
window.__ENV__ = {
  VITE_API_BASE_URL: "${VITE_API_BASE_URL:-}"
};
EOF

