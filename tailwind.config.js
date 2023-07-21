module.exports = {
    darkMode: 'class',
    theme: {
        extend: {},
    },
    plugins: [],
    content: [
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
