module.exports = {
    'root': true,
    'env': {
        'browser': true,
        'commonjs': true,
        'es6': true,
    },
    'parserOptions': {
        'sourceType': 'module',
    },
    'extends': [
        'eslint:recommended',
        'airbnb-base'
    ],
    'globals': {
        'CKEDITOR': true,
        'VERSION': true,
        'google': true,
        'UIkit': true,
    },
};
