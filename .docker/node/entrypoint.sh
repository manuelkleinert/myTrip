#!/bin/bash

cd /var/www/html

if [ ! -f /var/www/html/package.json ]; then
    echo "package.json not found, please add first before starting this container."
    exit 1;
fi

npm run install-workflow
npm run custom-icons
npm run dev-server