# Build stage
FROM node:lts-slim AS build-stage

WORKDIR /app

# Install build dependencies for native modules

# Copy package files
COPY package*.json ./

# Clean install with all optional dependencies
RUN npm install
RUN npm ci

# Copy project files
COPY . .

# Build the Vue.js application
# RUN npm run build
RUN npm run build-only

# Production stage
FROM node:lts-alpine AS production-stage

WORKDIR /app

# Install serve to host the built files
RUN npm install -g serve

# Copy built assets from build stage
COPY --from=build-stage /app/dist ./dist

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
  CMD wget --quiet --tries=1 --spider http://localhost:3000 || exit 1

# Start serve with SPA mode
CMD ["serve", "-s", "dist", "-l", "3000"]