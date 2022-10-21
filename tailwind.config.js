const colors = require('tailwindcss/colors');
const defaultTheme = require('tailwindcss/defaultTheme');

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './vendor/filament/**/*.blade.php',
        './vendor/awcodes/filament-curator/resources/views/**/*.blade.php'
    ],
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                gray: colors.neutral,
                danger: colors.rose,
                primary: colors.amber,
                success: colors.green,
                warning: colors.amber,
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans]
            }
        }
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography')
    ]
};
