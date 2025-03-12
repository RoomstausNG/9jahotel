import preset from "./vendor/filament/support/tailwind.config.preset";

export default {
    presets: [preset],
    content: [
        "./app/Filament/**/*.php",
        "./app/Filament/Hotel/Widgets/*.php",
        "./resources/views/filament/**/*.blade.php",
        "./resources/views/filament/*/widgets/.blade.php",
        "./vendor/filament/**/*.blade.php",
        "./resources/views/**/*.blade.php",
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
        "./node_modules/flowbite/**/*.js",
    ],
    theme: {
        extend: {
            backgroundImage: {
                one: "url('/img/bg-one.jpg')",
                two: "url('/img/bg-two.jpg')",
            },
            fontFamily: {
                cairo: ["Cairo"],
            },
            colors: {
                primary: {
                    // DEFAULT: "#DA2C23",
                    DEFAULT: "#009999",
                    DARK: "#001a1a",
                    light: "#e6ffff",
                    50: "#e6ffff",
                    100: "#ccffff",
                    200: "#99ffff",
                    300: "#66ffff",
                    400: "#00cccc",
                    500: "#009999",
                    600: "#008080",
                    700: "#006666",
                    800: "#004d4d",
                    900: "#003333",
                },
                secondary: {
                    DEFAULT: "#87CEEB",
                    DARK: "#332500",
                    light: "#fff8e6",
                    50: "#fff1cc",
                    100: "#ffebb3",
                    200: "#ffe499",
                    300: "#ffd666",
                    400: "#ffc933",
                    500: "#ffc21a",
                    600: "#e6a800",
                    700: "#cc9600",
                    800: "#997000",
                    900: "#664b00",
                },
                tertiary: "#0099CC",
                background: {
                    50: "#fafafd",
                    100: "#f0f0f5",
                    dark: "#010101",
                },
                available: "#ddd", // white
                pending: "#ffcc00", // yellow
                unpaid: "#ffcc00", // yellow
                booked: "#008000", // green
                paid: "#008000", // green
                occupied: "#FF0000", // red
                late: "#FFA500", // orange
                dirty: "#A52A2A", // brown
                repair: "#000000",
                Available: "#ddd", // white
                Pending: "#ffcc00", // yellow
                Unpaid: "#ffcc00", // yellow
                Paid: "#008000", // green
                Occupied: "#FF0000", // red
                Late: "#aa3300", // orange
                Dirty: "#A52A2A", // brown
                Repair: "#000000",
                income: "#05cc0a",
                expenditure: "#ee0016",
                Booked: "#008000", // green
            },
        },
    },
    plugins: [
        require("@tailwindcss/forms"),
        require("@tailwindcss/typography"),
        require("flowbite/plugin"),
    ],
};
