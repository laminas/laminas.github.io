# laminas.github.io

This is the primary documentation website for the [Laminas
Project](https://getlaminas.org), and contains:

- the landing page
- assets common to all documentation
- component lists

## Building assets

```bash
$ cd asset
$ npm install
$ gulp
```

Once they are built, test the main landing page, using something like:

```bash
$ php -S 0:8000 -t .
```

and then browsing to http://localhost:8000

Check in your changes to the `asset` directory as well as any generated assets
when done.

## Building the homepage

You can add or edit components in the file:

- `data/component-list.components.json`
- `data/component-list.mvc.json`

Whenever you do, update the pages with package lists using:

```bash
$ php build/build-component-lists.php components
```

and 

```bash
$ php build/build-component-lists.php mvc
```

Preview the project using:

```bash
$ php -S 0:8000 -t .
```

and then browsing to:

* http://localhost:8000
* http://localhost:8000/components
* http://localhost:8000/mvc

When done, check in changes to:

* `index.html`
* `components/index.html`
* `mvc/index.html`
