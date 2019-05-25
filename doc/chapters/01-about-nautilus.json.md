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
