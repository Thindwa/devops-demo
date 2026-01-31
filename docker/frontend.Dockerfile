FROM node:20-alpine AS build
WORKDIR /app

COPY package*.json ./
RUN npm install

COPY . .
RUN npm run build

FROM nginx:alpine
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY docker/docker-entrypoint.d/ /docker-entrypoint.d/
COPY --from=build /app/dist /usr/share/nginx/html

# Ensure env.js always exists (avoids 404 before entrypoint runs)
# and entrypoint scripts are executable.
RUN printf '%s\n' "// Generated at container start." "window.__ENV__ = { VITE_API_BASE_URL: \"\" };" > /usr/share/nginx/html/env.js \
  && chmod +x /docker-entrypoint.d/*.sh
EXPOSE 80

