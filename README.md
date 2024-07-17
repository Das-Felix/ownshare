# OwnShare - open-source filesharing

**This project is in a very early stage and under active development!**

OwnShare is an open-source filesharing software that can be used as a direct alternative to services like wetransfer or workupload. It used PHP as its backend and static HTML as its frontend so that it can be deployed anywhere. The frontend is built using SvelteKit and build using its static adapter.

## Features
![ownshare_3](https://github.com/Das-Felix/ownshare/assets/62439997/16a9b33a-69ad-42cd-9998-2cb20cf65157)
![ownshare_1](https://github.com/Das-Felix/ownshare/assets/62439997/2f518b0c-e9d5-4d46-acce-bbf2747173e9)
![ownshare_4](https://github.com/Das-Felix/ownshare/assets/62439997/c777e5d5-49aa-4aa8-8c3f-cf21acc9bf0e)
![ownshare_5](https://github.com/Das-Felix/ownshare/assets/62439997/a52fecb2-e6c2-4271-bbc3-9dbefa6808b2)
![ownshare_6](https://github.com/Das-Felix/ownshare/assets/62439997/26852954-9cfc-4f76-9e99-1afe620e0437)

## Custom Theme
You can customize the look of your download page by editing the template.html and style.css file inside the theme folder. The template.html file uses Handlebars as its template engine. You can look into the default theme files to see the structure.

## Installation
There are multiple ways of installing OwnShare. When Installed correctly, before using your app you have to go to /api/installer.php. This will create all the database tables and a default admin user. You will find more info on that page.

INFO: To my knowledge SvelteKit currently doesn't support loading assets from a relative path when using the static adapter! If you need to install OwnShare in a subfolder you need to adjust the **base** setting in the svelte.config.js, and then build the app from the sourcecode.

## Install from Release

Requirements: PHP Environment, MySQL Database

1. Download the latest release and upload it to your PHP Environment
2. follow the steps for manual configuration


## Building the app from the sourcecode

To make the build process as easy as possible, I created the build.py script that is used to build the svelte app, and then merge it with the backend.

Run it with: python3 build.py

This will create a build folder that contains the finished build, then follow the manual configuration.


## Manual configuration
For OwnShare to work there are two config files that have to be edited:

1. **config.json**<br>
    this file is the config file for the frontend and contains the backendAddress. If OwnShare is running on a subfolder it has to contain the absolute url to the backend. If not you can set it to /api<br>
    **Examples:**<br>
    www.your-domain.com - backendAddress: "/api"<br>
    www.your-domain.com/ownshare - backendAddress: "http://www.your-domain.com/ownshare/api"
2. **config.php**<br>
    this file contains the backend configuration.<br>
    **DB_HOST, DB_USER, DB_PASSWORD and DB_NAME** have to be set to your Database credentials.<br>
    **DB_CORS_URLS** can be set to "" when your backend and frontend are running on the same domain.


## Running with Docker
If you want to run OwnShare with docker, you can use the provided Dockerfile and docker-compose.yml file. The Dockerfile runs the setup.sh script as its entry point. This script loads the ENV Variables from the docker-compose file and automatically configures OwnShare.

1. Clone the repo
2. run the python build script - python3 build.py
3. configure the docker-compose.yml
4. build or run the image - for example: docker-compose up --build