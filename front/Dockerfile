# syntax=docker/dockerfile:1
FROM node:24-alpine AS build

WORKDIR /app

COPY package.json package-lock.json ./
# npm skips an optional native binary without failing when its download
# hiccups, and Docker would then cache that broken layer. Loading the two
# native modules here makes the step fail loudly instead.
RUN npm ci && node -e "require('lightningcss'); require('@tailwindcss/oxide')"

COPY . .
RUN npm run build

FROM nginx:1.27-alpine AS runtime

COPY nginx.conf /etc/nginx/conf.d/default.conf
COPY --from=build /app/dist /usr/share/nginx/html

EXPOSE 80
