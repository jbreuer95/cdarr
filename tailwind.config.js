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
        borderWidth: {
            default: '1px',
            '0': '0',
            '2': '2px',
            '3': '3px',
            '4': '4px',
            '6': '6px',
            '8': '8px',
        },
        extend: {
            spacing: {
                '15': '3.75rem',
                '52': '13.125rem',
            }
        },
    },
    variants: {
        borderWidth: ['responsive', 'focus'],
    },
    plugins: [],
}
