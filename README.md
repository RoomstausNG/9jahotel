# Nuxt UI Starter

Look at [Nuxt docs](https://nuxt.com/docs/getting-started/introduction) and [Nuxt UI docs](https://ui.nuxt.com) to learn more.

## Setup

Make sure to install the dependencies:

```bash
# npm
npm install

# pnpm
pnpm install

# yarn
yarn install

# bun
bun install
```

## Recommended: Bun

This project is optimized for [Bun](https://bun.sh/), a fast JavaScript runtime. For best results, use Bun for installing dependencies and running scripts:

```bash
bun install
bun run dev
```

You can also use npm, yarn, or pnpm, but Bun is recommended for speed and compatibility.

## Development Server

Start the development server on `http://localhost:3000`:

```bash
# npm
npm run dev

# pnpm
pnpm run dev

# yarn
yarn dev

# bun
bun run dev
```

## Production

Build the application for production:

```bash
# npm
npm run build

# pnpm
pnpm run build

# yarn
yarn build

# bun
bun run build
```

Locally preview production build:

```bash
# npm
npm run preview

# pnpm
pnpm run preview

# yarn
yarn preview

# bun
bun run preview
```

Check out the [deployment documentation](https://nuxt.com/docs/getting-started/deployment) for more information.

---

## Tauri Desktop App (v2)

This project supports running as a desktop app using [Tauri v2](https://tauri.app/start/).

### Prerequisites

- Node.js (v18+ recommended)
- Bun (for scripts)
- Rust & Cargo ([Install Rust](https://www.rust-lang.org/tools/install))
- Tauri CLI: `cargo install tauri-cli`

### Install dependencies

```bash
bun install
```

### Run Nuxt web app

```bash
bun run dev
```

Visit http://localhost:3000

### Run Tauri desktop app

```bash
cd src-tauri
cargo tauri dev
```

This will launch the desktop app (make sure Nuxt is running first).

### Build for production

- Nuxt: `bun run build`
- Tauri: `cd src-tauri && cargo tauri build`

### Environment variables

Copy `.env.example` to `.env` and fill in your secrets.

---

For more info and troubleshooting, see:

- [Nuxt Docs](https://nuxt.com/docs/getting-started/introduction)
- [Nuxt UI Docs](https://ui.nuxt.com)
- [Tauri Docs](https://tauri.app/start/)
