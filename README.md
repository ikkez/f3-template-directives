# Template directives
Collection of different template directives for the [PHP Fat-Free Framework](https://github.com/bcosca/fatfree).


### form

A collection of additional form-related HTML tag handlers for server side data handling to **form / input / select / textarea** elements.

Init:

```php
\Template\Tags\Form::initAll();
```

This automatically registers the following directives: `input`, `select`, `option`, `textarea`, `form`.

Any data you set to the global `POST` variable is filled into the registered form elements accordingly. If you want to use a different hive key, you can do it like this:

```php
// change source key
$f3->copy('POST','form1');
\Template\Tags\Form::instance()->setSrcKey('form1');
```

You can also fill the form fields dynamically based on the form name attribute:

```html
<form name="contact">
```

The field target is then set to `FORM.contact`:

```php
$f3->copy('POST','FORM.contact');
\Template\Tags\Form::instance()->setDynamicSrcKey(true);
```


### markdown

```html
<markdown>
# Headline

You can write **markdown** here
</markdown>
```

or

```html
<markdown src="path/to/file.md" />
```

Init:

```php
\Template\Tags\Markdown::init('markdown');
```


### image

Init:

```php
\Template\Tags\Image::init('image' [, $tmpl [, $options ] ] );
```

Options:

*  `temp_dir`, public accessable path for generated, temporary thumbnail images
*  `file_type`, default file type for dumped images, `png`, `jpeg`, `gif` or `wbmp`
*  `default_quality`, image quality, 0-100
*  `not_found_fallback`, fallback path for missing images

Usage:


```html
<image src="path/to/image.jpg" width="200" />
```

Additional attributes:

*  `width`, target image width
*  `height`, maximum image height
*  `crop`, allow image to be cropped into width/height ratio
*  `enlarge`, size up image when source image is smaller than target size
*  `quality`, overwrite default quality



