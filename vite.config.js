import { defineConfig } from "vite";
import laravel, { refreshPaths } from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/js/app.js",
                "resources/sass/main.scss",
            ],
            refresh: [
                ...refreshPaths,
                "./app/Filament/**",
                "./app/Forms/Components/**",
                "./app/Livewire/**",
                "./app/Infolists/Components/**",
                "./app/Providers/Filament/**",
                "./app/Tables/Columns/**",
                "./app/Filament/**/*.php",
                "./resources/views/filament/**/*.blade.php",
                "./vendor/filament/**/*.blade.php",
            ],
        }),
    ],
});
