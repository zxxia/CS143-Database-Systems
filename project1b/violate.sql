# Violate primary key restraints
INSERT INTO Actor VALUES (10, 'Smith', 'Tom', 'Male', 19990101, 1990102);
# mysql> INSERT INTO Actor VALUES (10, 'Smith', 'Tom', 'Male', 19990101, 1990102);
# ERROR 1062 (23000): Duplicate entry '10' for key 'PRIMARY'

UPDATE Director SET id = 63 WHERE id = 16;
# mysql> UPDATE Director SET id = 63 WHERE id = 16;
# ERROR 1062 (23000): Duplicate entry '63' for key 'PRIMARY'

UPDATE Movie SET id = 2 WHERE id = 3;
# mysql> UPDATE Movie SET id = 2 WHERE id = 3;
# ERROR 1062 (23000): Duplicate entry '2' for key 'PRIMARY'






# Violate referential integrity constraints
DELETE FROM Actor WHERE id = 19;
# It violates referential integrity but db fixes by cascading deletion.

# mysql> DELETE FROM Actor WHERE id = 19;
# Query OK, 1 row affected (0.00 sec)

UPDATE Movie SET id = 100000000, title = 'adsfasf' WHERE title = 'Die Another Day';
# It violates referential integrity but db fixes by cascading update referencing table.

# mysql> UPDATE Movie SET id = 100000000, title = 'adsfasf' WHERE title = 'Die Another Day';
# Query OK, 1 row affected (0.01 sec)
# Rows matched: 1  Changed: 1  Warnings: 0

DELETE FROM Director WHERE id = 16;
# It violates referential integrity but db fixes by cascading deletion.

# mysql> DELETE FROM Director WHERE id = 16;
# Query OK, 1 row affected (0.01 sec)

INSERT INTO MovieGenre VALUES (0,'Documentary');
# It violates referential integrity since newly inserted id 0 is not in Movie table.

# mysql> INSERT INTO MovieGenre VALUES (0,'Documentary');
# ERROR 1452 (23000): Cannot add or update a child row: a foreign key constraint fails (`CS143`.`MovieGenre`, CONSTRAINT `MovieGenre_ibfk_1` FOREIGN KEY (`mid`) REFERENCES `Movie` (`id`) ON DELETE CASCADE ON UPDATE CASCADE)


INSERT INTO MovieDirector VALUES (-1, -1);
# It violates referential integrity since newly inserted did -1 and mid -1 are not
# in Director table and Movie table respectively.

# mysql> INSERT INTO MovieDirector VALUES (-1, -1);
# ERROR 1452 (23000): Cannot add or update a child row: a foreign key constraint fails (`CS143`.`MovieDirector`, CONSTRAINT `MovieDirector_ibfk_1` FOREIGN KEY (`mid`) REFERENCES `Movie` (`id`) ON DELETE CASCADE ON UPDATE CASCADE)

INSERT INTO MovieActor VALUES (123, 111111111111111, 'hero');
# It violates referential integrity since newly inserted id -2 is not in Movie table.

# mysql> INSERT INTO MovieActor VALUES (123, 111111111111111, 'hero');
# ERROR 1452 (23000): Cannot add or update a child row: a foreign key constraint fails (`CS143`.`MovieActor`, CONSTRAINT `MovieActor_ibfk_1` FOREIGN KEY (`mid`) REFERENCES `Movie` (`id`) ON DELETE CASCADE ON UPDATE CASCADE)





# Violate check constraints
INSERT INTO Review VALUES ('Ace', 12, 2, 100, "very good");
# It violates check restraints where rating 100 > 5.

INSERT INTO Actor VALUES (-10, 'Smith', 'Tom', 'Male', 19990101, 1990102);
# It violates check restraints by adding id < 0.

UPDATE Movie SET id = 2 WHERE id = 0;
# It violates check restraints by updating id to 0.