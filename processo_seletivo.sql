create database processo_seletivo default charset=utf8mb4;

create table users(id int auto_increment primary key, name varchar(30), email varchar(30), password varchar(30), drink_counter int);

create table drink_history(id int auto_increment primary key, user_id int, ml int, data datetime);

ALTER TABLE `drink_history` ADD CONSTRAINT `fk_id` FOREIGN KEY ( `user_id` ) REFERENCES `users` ( `id` );