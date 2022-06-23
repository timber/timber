---
name: Release Checklist (1.x)
about: "\U0001F512 Maintainers only: create a checklist for a regular release process"

---

## readme.txt
- [ ] Update changelog info
- [ ] Update "Test up to" version
- [ ] Update Stable tag

## GitHub
- [ ] Publish Release

## PHP files
- [ ] Update version in `lib/Timber.php`
- [ ] Update version in `bin/Timber.php`

## Deploy to WordPress.org
- [ ] Run `./bin/deploy-to-wp-org 1.20.1`
- [ ] Publish new tag for version
- [ ] Update `trunk`

## Twitter
- [ ] Tweet it out!
```It's a big release day! Version 1.20 is now live with some fixes for #WordPress 6.0 compatibility```