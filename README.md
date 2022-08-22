# homebase
A simple PHP script for storing data from [Overland](https://overland.p3k.app/) in an SQLite database.

```
Logging URL: https://$INSTANCE_URL/?key=$KEY
Last 500 Rows: https://$INSTANCE_URL/?key=$KEY&csv=true
All Rows: https://$INSTANCE_URL/?key=$KEY&csv=true&full=true
```
## config.json
Rename `config.example.json` to `config.json` and edit the values.

- `key` can be any value you want and is used as authorization
- `enable_csv_endpoint` enables and disables the CSV API endpoint

## Setup
Point your server to `public/index.php` and enjoy!

## Requirements
- Requires `sqlite3` extension
- Tested in PHP 8.1
