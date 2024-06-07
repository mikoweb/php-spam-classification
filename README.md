# php-spam-classification

The aim of the project is to create an application for classifying spam in emails. The application will analyze the content of emails and assign them a label indicating whether they are spam or not.

The application was written using, among others: Rubix ML library and Random Forest classifier. 

The REST API was made in Symfony and the frontend in Angular.

## Most important classes

* [LearnerFactory](./src/Module/ML/Application/Model/LearnerFactory.php)
* [SpamModelTrainer](./src/Module/ML/Application/Model/SpamModelTrainer.php)
* [SpamModelReport](./src/Module/ML/Application/Model/SpamModelReport.php)
* [SpamModelTester](./src/Module/ML/Application/Model/SpamModelTester.php)
* [Constant](./src/Module/ML/Domain/Constant.php)
* [SpamDatasetReader](./src/Module/ML/Infrastructure/Reader/SpamDatasetReader.php)

## Example of using prediction

For a complete usage example, see the class:

* [AskForPredictionHandler](./src/Module/ML/Application/Interaction/Query/AskForPrediction/Handler/AskForPredictionHandler.php)

## Commands

```
Available commands for the "app" namespace:
  app:ml:train   Command to training a spam classification model.
  app:ml:report  Command to generate a report about the model.
  app:ml:test    Command to testing a spam classification model.
```

## Demo

The application demo is available at:

    https://spam-classification.mikoweb.usermd.net/

## Copyrights

Copyright © Rafał Mikołajun 2024.
