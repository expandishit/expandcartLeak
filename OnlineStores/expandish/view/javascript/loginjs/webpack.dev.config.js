const path = require("path");
const { CleanWebpackPlugin } = require("clean-webpack-plugin");
const HtmlWebpackPlugin = require("html-webpack-plugin");

module.exports = {
    externals: {
        jquery: "jQuery",
    },
    entry: {
        loginjs: "./src/index.js",
    },
    output: {
        filename: "[name].bundle.js",
        path: path.resolve(__dirname, "./dist"),
        publicPath: "",
        libraryExport: "default",
        library: "Loginjs",
        libraryTarget: "umd",
        globalObject: "this",
    },
    mode: "development",
    optimization: {
        splitChunks: {
            chunks: "all",
            minSize: 2000,
            automaticNameDelimiter: "_",
        },
    },
    devServer: {
        contentBase: path.resolve(__dirname, "./dist"),
        index: "login.html",
        port: 9000,
        writeToDisk: true,
    },
    module: {
        rules: [
            {
                test: /\.(png|jpg)$/,
                use: ["file-loader"],
            },
            {
                test: /\.css$/,
                use: ["style-loader", "css-loader"],
            },
            {
                test: /\.scss$/,
                use: ["style-loader", "css-loader", "sass-loader"],
            },
            {
                test: /\.js$/,
                exclude: /node_modules/,
                use: {
                    loader: "babel-loader",
                    options: {
                        presets: ["@babel/env"],
                        plugins: [
                            "transform-class-properties",
                            "@babel/transform-runtime",
                        ],
                    },
                },
            },
            {
                test: /\.hbs$/,
                use: ["handlebars-loader"],
            },
            {
                test: /\.(woff2|woff|ttf)$/,
                use: [
                    {
                        loader: "file-loader",
                        options: {
                            name: "[name].[ext]",
                            outputPath: "fonts/",
                        },
                    },
                ],
            },
        ],
    },
    plugins: [
        new CleanWebpackPlugin(),
        new HtmlWebpackPlugin({
            filename: "login.html",
            chunks: ["loginjs", "vendors_loginjs"],
            title: "Login Page",
            description: "login Page",
            inject: false,
            templateContent: ({ htmlWebpackPlugin }) => {
                const jsFiles = htmlWebpackPlugin.files.js.reduce(
                    (a, b) =>
                        (a +=
                            '<script type="text/javascript" src="' +
                            b +
                            '"></script>'),
                    ""
                );
                const cssFiles = htmlWebpackPlugin.files.css.reduce(
                    (a, b) => (a += '<link rel="stylesheet" href="' + b + '">'),
                    ""
                );
                return `<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="description" content="${htmlWebpackPlugin.options.description}"><title>${htmlWebpackPlugin.options.title}</title><link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">${cssFiles}</head><body><div class="container-fluid"><a class="btn btn-link" href="http://qaz123.expandcart.com/index.php?route=account/login">Sign In</a></div><script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script><script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script><script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>${jsFiles}<script>window.addEventListener('DOMContentLoaded', (event) => { window.Loginjs !== undefined && (window.login = new Loginjs({ storeName: 'QAZ123', lang: 'en', storeCode: 'qaz123',ajax: {baseURL: "http://qaz123.expandcart.com",}, countryId: '63', enableMultiseller: 0, loginWithPhone: 0, customer: {}, map: {}, loginSelectors: { customer: { login: ['a[href*="http://qaz123.expandcart.com/index.php?route=account/login"]'] } }, socialLogin: { status: 0 }, countries: [], libraryStatus: 'on' }).render()); });</script></body></html>`;
            },
        }),
    ],
};
