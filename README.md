# Magerun Download Remote Media

A n98-magerun module to download product images from a production server.

## Installation

You just need to clone this repo into a ~/.n98-magerun/modules folder, then magerun will pick it up.

    cd ~
    mkdir -p .n98-magerun/modules
    cd .n98-magerun/modules
    git clone git@bitbucket.org:meanbee/download-remote-media.git
    cd download-remote-media
    composer install

## Usage

Fetch all images for all products:

    n98-magerun.phar media:fetch:products --remote-url=http://www.clientwebsite.com/
    
Fetch only specific products by SKUs:

    n98-magerun.phar media:fetch:products --remote-url=http://www.clientwebsite.com/ --skus=abc1,abc2,abc3,abc4
    
Only want certain image attributes downloaded? No problem!
    
    n98-magerun.phar media:fetch:products --remote-url=http://www.clientwebsite.com/ --image-attributes=small_image,custom_image
