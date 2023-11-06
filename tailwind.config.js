/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    theme: {
        fontFamily: {
            sans: ["Roboto"],
            mono: ['"Roboto Mono"'],
        },
        extend: {},
    },
    plugins: [],
};
