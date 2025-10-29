# Troubleshooting

Common issues and their solutions during installation.

## Installation Issues

### Composer Installation Fails

**Problem**: Composer cannot install the extension.

**Solution**:
1. Check your PHP version: `php -v`
2. Update Composer: `composer self-update`
3. Clear Composer cache: `composer clear-cache`

### Permission Errors

**Problem**: File permission errors during installation.

**Solution**:
1. Check file permissions: `ls -la`
2. Set correct permissions: `chmod -R 755`
3. Ensure web server has write access

## Configuration Issues

### Module Not Visible

**Problem**: Documentation module not visible in backend.

**Solution**:
1. Check user permissions
2. Verify extension is enabled
3. Clear TYPO3 caches
