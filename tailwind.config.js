module.exports = {
    darkMode: 'class',
    content: [],
    theme: {
        extend: {},
    },
    variants: {
        extend: {},
    },
    plugins: [],
    purge: [
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    safelist: [
        {
            pattern: /bg-/,
            variants: ['dark', 'hover', 'focus', 'dark:hover'],
        },
        {
            pattern: /text-/,
            variants: ['dark', 'hover', 'focus', 'dark:hover'],
        }
    ],
}
