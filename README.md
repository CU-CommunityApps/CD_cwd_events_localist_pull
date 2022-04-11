# CWD Localist Events Importer

A Drupal 8 / 9 custom module to import events from localist (CU Calendar, Weill Calendar, etc.) into nodes of a specified content type.

[![Latest Stable Version](https://img.shields.io/packagist/v/cubear/cwd_events_localist_pull.svg?style=flat-square)](https://packagist.org/packages/cubear/cwd_events_localist_pull)

## Usage

### Install with composer
```
composer require cubear/cwd_events_localist_pull
```

### Configure
Work-in-progress instructions!

After you enable the module on a Drupal 8/9 site, go to Admin > Configuration > Web services > Localist Events Pull.  From there, create one or more "Localist Pull entities."  The URL should look like: `https://events.cornell.edu/api/2/events?api_key=YOUR_KEY`.

Do not skip:
* Event Machine Name
* Localist ID field (this field will be used as the unique ID for imported event entities)
