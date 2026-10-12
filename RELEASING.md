# Releasing Mortgage Calculators WP

## 1. Bump the version (all three must match)

- `mortgage-calculators-wp.php` — `Version:` header
- `mortgage-calculators-wp.php` — `MC_VERSION` constant
- `readme.txt` — `Stable tag:` (and add a Changelog entry)

## 2. Build the zip

The zip must contain a single top-level folder named `mortgage-calculators-wp/`
(WordPress installs updates into the folder name inside the zip). Leave out `.git`,
`.gitignore` and `RELEASING.md`.

Name the file exactly `mortgage-calculators-wp.zip`.

## 3. Tag and publish the GitHub release

**The tag must be `v` + the version, e.g. `v2.4.0`.**

Sites without an EDD download link fall back to:

```
https://github.com/toddlenderd/mortgagecalculatorswp/releases/download/v{version}/mortgage-calculators-wp.zip
```

Every installed copy since 2.2 builds that URL, so a release tagged without the `v`
(like `2.3.1`) returns 404 and those sites can't update. Don't change the format in
the code; old installs would never see it.

```bash
git tag v2.4.0
git push origin v2.4.0
gh release create v2.4.0 mortgage-calculators-wp.zip --title "Mortgage Calculators WP 2.4.0" --notes-file notes.md
```

## 4. Update EDD

Upload the same zip to the EDD product (ID 1726) on mortgagecalculatorsplugin.com and
set its version, so licensed sites get it through EDD.

## Front-end JavaScript

`assets/js/bundle.js` is the source of truth and is edited directly; there is no build
step. It starts with a small adapter that gives native `<input type="range">` sliders the
part of the bootstrap-slider API the calculator code uses, followed by the calculator code
(from `$mcwp(function ($) {` onward).

## Stylesheets

- `assets/css/wpmc-modern.css`: the Modern style (default). All color comes from the
  `--mcalc-accent` variable the plugin sets from the Calculator Color setting.
- `assets/css/wpmc.css`: the Classic style, kept for sites with custom CSS written
  against the original design.

Only one of the two loads, based on General Settings > Calculator Style.
