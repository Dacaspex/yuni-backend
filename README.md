# yuni-backend
Backend software for Yuni Android application

## About
This project is the back-end software for the Yuni application. Yuni is an Android application developed for the course 
2IS70Q4 (2019) at the Technical university of Eindhoven (TU/e). The purpose of Yuni is to provide students access to information 
about the canteens present on the TU/e campus. This information entails, but is not limited to: Information about a canteen, the menu of a canteen, 
reviews for both canteens and menu items. Canteen owners can login into the application and update their information.

## Purpose
This PHP application supports the back-end that is required for the Yuni application to operate. Its only function is to
expose an API to the application to retrieve, create and update inforation. This software is responsible for storing in and
retrieving information from a database (Storage). 

## Installation
Clone and install the project.
```
git clone https://github.com/Dacaspex/yuni-backend.git
cd yuni-backend
composer install
```
Copy and configure configuration files.
```
# yuni-backend/.env
DB_DSN=''
DB_USER=''
DB_PASS=''
```
```
# yuni-backend/auth.json
{
  "tokens": [
    {
      "token": "<replace with your api token>"
    }
  ]
}
```
Optionally, you can seed the database with values
```
bin/console db:seed
```

## Licensing
All of this work is made for the Technical University of Eindhoven, who owns the full rights to this software. 
