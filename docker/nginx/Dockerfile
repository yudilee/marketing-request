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

# Set working directory
WORKDIR /var/www/html

# 1. Copy everything from the host public folder
COPY public public

# 2. Force clean and explicitly copy the build folder from the build stage
RUN rm -rf public/build && mkdir -p public/build
COPY --from=build-stage /app/public/build public/build

# 3. Create a verification file
RUN date > public/check.txt

# 4. Set permissions
RUN chmod -R 755 public
