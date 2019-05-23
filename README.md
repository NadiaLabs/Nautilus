Nautilus - Yet Another Document Generator
=========================================

You can use Nautilus to generate Markdown documents into one HTML file (including images).

You can see [`doc`](./doc) as a quick example.

### Steps for write a documents:

1. Create a directory for document files.
2. Create a `nautilus.json` file to setup your document configuration.
3. Write your document contents with Markdown format (see [Markdown manual](https://daringfireball.net/projects/markdown/syntax) and [Markdown Extra manual](https://michelf.ca/projects/php-markdown/extra/)).
4. Run the command `php nautilus.phar generate` at your document directory.

## About `nautilus.json`

This is a full example of `nautilus.json`:

```json
{
    "documents": [
        {
            "title": "Document Title",
            "outputFilePath": "output/index.html",
            "theme": "default",
            "parameters": {
                "version": "v1.0.0",
                "updateAt": "2019/05/12 21:06:00"
            },
            "markdownOptions": {
                "tableClasses": "table table-bordered w-auto mx-auto"
            },
            "chapters": [
                {
                    "title": "Chapter 1: Introduction",
                    "filePath": "chapters/chapter01.md",
                    "parameters": {}
                },
                {
                    "title": "Chapter 2: Requirements",
                    "filePath": "chapters/chapter02.md",
                    "parameters": {}
                },
                {
                    "title": "Chapter 3: Examples",
                    "filePath": "chapters/chapter03.md",
                    "parameters": {}
                }
            ]
        }
    ],
    "parameters": {
        "foo": "bar"
    }
}
```

| key name   | required | description |
| ---------- | -------- | ----------- |
| documents  | Y        | Every document file configuration. |
| parameters | N        | The parameters using in markdown and themes (PHP templates). |

#### `documents` configuration

| key name        | required | description |
| --------------- | -------- | ----------- |
| title           | Y        | The title of the document. |
| outputFilePath  | Y        | The output file path for generated document file (e.g.: output/index.html). |
| theme           | N        | The template file path (absolute path, or related path with working directory). Default: "default" |
| parameters      | N        | The parameters using in markdown and themes (PHP templates). Default: {} |
| markdownOptions | N        | The markdown options. Default: {} |
| chapters        | Y        | Every document include many chapters, this is the configuration collection of each chapter. |

#### `chapters` configuration

| key name        | required | description |
| --------------- | -------- | ----------- |
| title           | Y        | The chapter title. |
| filePath        | Y        | The chapter markdown file, file contents is `Markdown` format (e.g.: chapters/chapter01.md). |
| parameters      | N        | The parameters using in markdown and themes (PHP templates). Default: {} |

#### `markdownOptions` configuration

| key name      | required | description |
| ------------- | -------- | ----------- |
| enablePhpEval | N        | Enable PHP `eval` function to run PHP scripts, and render document contents. Default: false |
| workingDir    | N        | Setup the working directory path, default is the current working directory of the console. |
| tableClasses  | N        | The `table` tag class attribute. Default: "table table-bordered" |
| theadClasses  | N        | The `thead` tag class attribute. Default: "table-active" |


## Console command

### `php nautilus.phar generate --help`

This command will generate document files with the `nautilus.json` configuration.

```
Usage:
  generate [options]

Options:
  -e, --enable-php-eval  Enable PHP eval function
  -h, --help             Display this help message
  -q, --quiet            Do not output any message
  -V, --version          Display this application version
      --ansi             Force ANSI output
      --no-ansi          Disable ANSI output
  -n, --no-interaction   Do not ask any interactive question
  -v|vv|vvv, --verbose   Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Help:
  Generate documents
```

## Special Markups

### Colored Text

examples:

- `{c:[red]}color text{/c}`  
- `{c:[#abc]}color text{/c}`

### Use PHP `eval` to run PHP scripts and show block content

examples:

~~~
```eval-php   
$foo = 'bar'; 
echo $foo;

// Can use parameters from nautilus.json
$bar = $parameter;
echo $bar;
```
~~~

### Use PHP `eval` to run PHP scripts and show inline content

examples:

- `@$foo='bar'; echo 'foobar';@` 
- `@$foo=$parameter; echo $foo;@` Can use parameters from nautilus.json
