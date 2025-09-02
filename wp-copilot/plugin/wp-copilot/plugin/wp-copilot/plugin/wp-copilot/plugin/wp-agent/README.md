# WP Agent (from scratch)

Small Node.js CLI to create, read, update, delete, and list WordPress files via SFTP or cPanel UAPI.

## Quick Start
- Copy `.env.example` to `.env` and fill credentials.
- Install deps: `npm install`
- Try a command: `node src/index.js --driver sftp --cmd ls --remote /public_html/wp-content`

## Commands
- `ls`: List directory. `--remote /path`
- `get`: Print file to stdout. `--remote /path/file`
- `put`: Upload text file. `--local ./local.txt --remote /path/file`
- `mkdir`: Create directory. `--remote /path/newdir`
- `rm`: Delete file. `--remote /path/file`
- `rmdir`: Remove empty directory. `--remote /path/dir`

## Drivers
- `--driver sftp`: Uses SFTP (recommended). Needs SFTP_* vars.
- `--driver cpanel`: Uses cPanel UAPI. Needs CPANEL_* vars.

Examples
- SFTP list: `node src/index.js --driver sftp --cmd ls --remote /public_html/wp-content/themes`
- SFTP upload: `node src/index.js --driver sftp --cmd put --local ./header.php --remote /public_html/wp-content/themes/yourtheme/header.php`
- cPanel mkdir: `node src/index.js --driver cpanel --cmd mkdir --remote /home/USER/public_html/newdir`
- cPanel save file: `node src/index.js --driver cpanel --cmd put --local ./robots.txt --remote /home/USER/public_html/robots.txt`

Notes
- For binary uploads via cPanel, this CLI uses `save_file_content` (best for text). For large/binary assets, prefer SFTP.
- Operate under `wp-content/` when possible to avoid breaking core files.

