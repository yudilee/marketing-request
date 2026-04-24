FROM nginx:stable-alpine

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# We no longer build assets here, they are shared via volume from the app container
