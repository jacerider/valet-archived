# Valet

**A site navigation tool similar to Alfred for MacOS.**

## How To Use

Install and enable module as normal. Settings are available at:

```
https://<domain>/admin/config/user-interface/valet
```

By default, Valet can be invoked by pressing SHIFT+SPACE at the same time,
although it can be changed in settings.

Once invoked, type in a portion of an admin page title. Below the box will
show up a listing of admin pages or other items. (See settings page for
enabling other types of pages.) Use the "Up" and "Down" arrows on your keyboard
to select them item you want and then hit "Enter" to select the item. The
selected page will immediately be loaded.

## Development Setup

First, you will need to install [NodeJS](https://nodejs.org/en/download/package-manager/).

Run the following from the command line:

```bash
cd dev/
npm install
```

Install the following Ruby Gem from the command line:

```bash
gem install scss_lint
```

Make a copy of `example.config.js` and set your local development settings here.
Add this file to your `.gitignore` file to prevent breaking of team-members' setups.

```bash
cp example.config.js config.js
```

Run the following from the command-line from the module directory to have Gulp
compile and watch for changes to both `.scss` files and `.js` files found within
the `dev/` folder.

```bash
gulp
```
