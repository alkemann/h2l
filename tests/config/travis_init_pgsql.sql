DROP DATABASE IF EXISTS travis_ci_test;
CREATE DATABASE travis_ci_test;
USE travis_ci_test;
CREATE TABLE tests ( id INT UNSIGNED NOT NULL AUTO_INCREMENT , name VARCHAR(256) NOT NULL DEFAULT 'A name' , age INT UNSIGNED NOT NULL DEFAULT '69' , PRIMARY KEY (id)) ENGINE = InnoDB;