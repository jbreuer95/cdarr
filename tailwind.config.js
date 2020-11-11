module.exports = {
    future: {
        // removeDeprecatedGapUtilities: true,
        // purgeLayersByDefault: true,
    },
    purge: [
        './resources/views/**/*.blade.php'
    ],
    theme: {
        screens: {
            'xs': '414px',
            'sm': '640px',
            'md': '768px',
            'lg': '1024px',
            'xl': '1280px',
        },
        extend: {},
    },
    variants: {
        borderWidth: ['responsive', 'focus'],
    },
    plugins: [],
}
