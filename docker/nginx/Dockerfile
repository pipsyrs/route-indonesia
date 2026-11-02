# syntax=docker/dockerfile:1
# Build via compose only: "app" is an additional context (service:app)
FROM nginx:1.27-alpine

COPY --from=app /var/www/html/public /var/www/html/public
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
RUN mkdir -p /srv/storage
