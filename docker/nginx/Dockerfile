# Stage 1: Build assets
FROM node:20-alpine AS build-stage
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# Stage 2: Serve with Nginx
FROM nginx:stable-alpine
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Copy host public files first (images, robots.txt, etc.)
COPY public /var/www/html/public

# Copy built assets from build-stage (this merges the 'build' folder)
COPY --from=build-stage /app/public /var/www/html/public

# Ensure Nginx can read the files
RUN chmod -R 755 /var/www/html/public
