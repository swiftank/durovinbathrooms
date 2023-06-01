##Product Label Extension

This extension is used to highlight your products using different custom labels like "New", "Best Seller", "Most Popular", "Sale" etc...

##Support: 
version - 2.1.x, 2.2.x, 2.3.x

##How to install Extension

1. Download the archive file.
2. Unzip the file
3. Create a folder [root]/app/code/Etatvasoft/ProductLabel
4. Drop/move the unzipped folder to directory 'Magento_Root/app/code/Etatvasoft/ProductLabel'

#Enable Extension:
- php bin/magento module:enable Etatvasoft_ProductLabel
- php bin/magento setup:upgrade
- php bin/magento cache:clean
- php bin/magento setup:static-content:deploy
- php bin/magento cache:flush

#Disable Extension:
- php bin/magento module:disable Etatvasoft_ProductLabel
- php bin/magento setup:upgrade
- php bin/magento cache:clean
- php bin/magento setup:static-content:deploy
- php bin/magento cache:flush