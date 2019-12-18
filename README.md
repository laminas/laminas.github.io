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
