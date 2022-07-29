# Upgrade SQL process
Please follow the below steps to deliver your sql fix/changes to all customers
1. create a file for your ticket (eg. EC-14567.sql)
2. put all your changes there.
3. make sure that executing your file twice will not fail or delete data for existing customers

Note: all files in this folder will be executed only on already existing customers. If your fix/change needs to be delivered for new customers, please change the related table file in "database" folder as well.
