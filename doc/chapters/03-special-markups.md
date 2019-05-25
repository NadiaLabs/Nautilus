### Colored Text

Examples:

- `{c:[red]}color text{/c}`  
- `{c:[#abc]}color text{/c}`

### Use PHP `eval` to run PHP scripts and show block content

Examples:

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

Examples:

- `@$foo='bar'; echo 'foobar';@` 
- `@$foo=$parameter; echo $foo;@` Can use parameters from nautilus.json
