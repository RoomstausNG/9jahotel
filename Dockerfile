FROM oven/bun:latest

WORKDIR /app

# Copy package.json first for better layer caching
COPY package.json ./

# Install dependencies
RUN bun install --production

# Copy source code
COPY . .

# Build the project
RUN bun run build --sourcemap --minify --bundle

# dbpull
RUN bun run db:pull

# Expose the port your app runs on
EXPOSE 3003

# Command to run the application
CMD ["bun", "run", "dist/index.js"]
