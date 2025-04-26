module.exports = {
  apps: [
    {
      name: "wakalor",
      script: "bun",
      args: "dist/index.js",
      instances: 3,
      exec_mode: "cluster",
      interpreter: "bun",
      watch: true,
      max_memory_restart: "6G",
      env: {
        NODE_ENV: "production",
      },
      env_production: {
        NODE_ENV: "production",
      },
    },
  ],
};

// export const apps = [
//   {
//     name: "wakalor",
//     script: "bun",
//     args: "run dist/index.js",
//     instances: 2,
//     exec_mode: "cluster",
//     watch: false,
//     max_memory_restart: "8G",
//     env: {
//       NODE_ENV: "production",
//     },
//     env_production: {
//       NODE_ENV: "production",
//     },
//   },
// ];
