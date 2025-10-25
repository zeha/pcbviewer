# Security Policy

## Supported Versions

Currently supporting the latest version on the main branch.

| Version | Supported          |
| ------- | ------------------ |
| main    | :white_check_mark: |

## Security Features

This project implements multiple security measures:

### Input Validation
- **Allowlist validation** for all user inputs (theme, project, file parameters)
- **Path traversal prevention** via basename() and directory restrictions
- **HTML escaping** for all dynamic output
- **No direct file path usage** from user input

### File Access Controls
- Projects limited to `htdocs/projects/` directory
- Files validated against discovered project files only
- No arbitrary file system access

### Automated Security Scanning
- ShellCheck for shell script vulnerabilities
- PHP syntax and security checks
- Trivy for vulnerability scanning
- CodeQL for code analysis
- TruffleHog for secret detection
- Weekly automated security scans

## Reporting a Vulnerability

If you discover a security vulnerability, please report it responsibly:

1. **Do NOT** open a public GitHub issue
2. Email the maintainer directly (check git log for contact)
3. Include:
   - Description of the vulnerability
   - Steps to reproduce
   - Potential impact
   - Suggested fix (if available)

### Response Timeline

- **Initial Response**: Within 48 hours
- **Status Update**: Within 7 days
- **Fix Timeline**: Depends on severity
  - Critical: Within 7 days
  - High: Within 14 days
  - Medium: Within 30 days
  - Low: Next release cycle

## Security Best Practices

### Deployment
- Serve only the `htdocs/` directory via web server
- Keep update scripts outside document root
- Set appropriate file permissions (644 for files, 755 for directories)
- Use HTTPS in production
- Enable PHP opcache and disable dangerous functions

### Recommended php.ini Settings
```ini
expose_php = Off
display_errors = Off
log_errors = On
disable_functions = exec,passthru,shell_exec,system,proc_open,popen
open_basedir = /path/to/pcbviewer/htdocs
```

### Web Server Configuration

**Apache (.htaccess in root)**
```apache
# Deny access to git files
<DirectoryMatch "\.git">
    Require all denied
</DirectoryMatch>

# Deny access to shell scripts
<FilesMatch "\.(sh|log)$">
    Require all denied
</FilesMatch>
```

**Nginx**
```nginx
location ~ /\.git {
    deny all;
}

location ~ \.(sh|log)$ {
    deny all;
}
```

## Vulnerability Disclosure

Once a vulnerability is fixed:
1. Security advisory will be published
2. CVE will be requested if applicable
3. Credits will be given to reporter
4. Fix will be released with security notes
