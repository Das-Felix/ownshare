#!/bin/bash

set -e

echo "Loading environment variables"

# Check for missing environment variables
missing_vars=()

: "${DB_HOST:?missing}"
: "${DB_NAME:?missing}"
: "${DB_USER:?missing}"
: "${DB_PASSWORD:?missing}"
: "${ROOT_URL:?missing}"
: "${BACKEND_ADDRESS:?missing}"

for var in DB_HOST DB_NAME DB_USER DB_PASSWORD ROOT_URL BACKEND_ADDRESS; do
  if [ -z "${!var}" ]; then
    missing_vars+=("$var")
  fi
done

if [ ${#missing_vars[@]} -ne 0 ]; then
  echo "Error: The following environment variables are missing: ${missing_vars[*]}"
  echo "Required vars: DB_HOST, DB_NAME, DB_USER, DB_PASSWORD, ROOT_URL, BACKEND_ADDRESS"
  exit 1
fi

build_dir="/var/www/html"

# Set the contents of ./build/config.json
config_json_path="${build_dir}/config.json"
config_json_content=$(cat <<EOF
{
    "backendAddress": "${BACKEND_ADDRESS}"
}
EOF
)

echo "${config_json_content}" > "${config_json_path}"
echo "Set ${config_json_path} to ${config_json_content}"

# Set the contents of ./build/api/config.php
config_php_path="${build_dir}/api/config.php"
config_php_content=$(cat <<EOF
<?php
define("APP_SETUP_COMPLETE", false);

define("APP_CORS_URLS", "${ROOT_URL}");
define("APP_DB_HOST", "${DB_HOST}");
define("APP_DB_NAME", "${DB_NAME}");
define("APP_DB_USER", "${DB_USER}");
define("APP_DB_PASSWORD", "${DB_PASSWORD}");
define("APP_THEME_FOLDER", "/themes");
?>
EOF
)

mkdir -p "${build_dir}/api"
echo "${config_php_content}" > "${config_php_path}"
echo "Set ${config_php_path} to provided database and URL configuration"

echo "setting permissions"
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html
chown -R www-data:www-data /var/www/html/api
chmod -R 775 /var/www/html/api

exec apache2-foreground