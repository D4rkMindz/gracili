# Versioning

The versioning of the your-app uses the semver versioning model.

## Version increasing

### Increasing the major version

A major version (1.x.x) is always increased, whenever a breaking feature is introduced.

### Increasing the minor version

A minor version (x.1.x) is always increased, whenever a new feature was introduced.
This feature must improve an existing feature and not be on its own.

### Increasing the patch version

A patch version (x.x.1) is always increased, whenever a patch of a feature or a bug fix is introduced.

## Publish a new version

A new version can be published by using the following command:

```bash
$ php bin/increase-version.php -v <version_number> -n <version_note_file_path>
$ php bin/increase-version.php -v 1.0.0 -n VERSION_NOTES.md
```

The script checks out the master and merges the develop branch.
After that, a tag is created. All changes are pushed to the repository afterwards.

## Version display

The current version is saved in the `public/version` file.
This file is publicly accessible.