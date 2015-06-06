# Installation

## Composer (recommended)

Add `"monomelodies/improse"` to your `composer.json` requirements:

```json
{
    "require": {
        "monomelodies/improse": ">=0.4"
    }
}
```

...and run `$ composer update` from your project's root.

## Manual installation
1. Get the code;
    1. Clone the repository, e.g. from GitHub;
    2. Download the ZIP (e.g. from Github) and extract.
2. Make your project recognize Improse:
    1. Register `/path/to/improse/src` for the namespace `Improse\\` in your
       PSR-4 autoloader (recommended);
    2. Alternatively, manually `include` the files you need.

